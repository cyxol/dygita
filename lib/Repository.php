<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;

/** Data access for public content and counters. No HTML or request handling. */
final class Dygita_Repository
{

    private static array $counts = [];

    public static function preload(array $cids): void {
        $cids = array_values(array_filter(array_unique(array_map('intval', $cids))));
        $missing = array_values(array_filter($cids, fn($id) => !isset(self::$counts[$id])));
        if (!$missing) return;
        foreach ($missing as $id) self::$counts[$id] = ['likes' => 0, 'views' => 0];
        $db = \Typecho\Db::get();
        foreach ($db->fetchAll($db->select('cid','name','int_value')->from('table.fields')
            ->where('cid IN ?', $missing)->where('name IN ?', ['likes','views'])) as $row) {
            self::$counts[(int) $row['cid']][$row['name']] = (int) $row['int_value'];
        }
    }

    public static function counter(int $cid, string $name): int {
        self::preload([$cid]);
        return self::$counts[$cid][$name] ?? 0;
    }

    public static function increment(int $cid, string $name): int {
        if ($cid < 1 || !in_array($name, ['likes','views'], true)) throw new \InvalidArgumentException('Invalid counter');
        $db = \Typecho\Db::get();
        $table = $db->getPrefix() . 'fields';
        // MariaDB/MySQL atomic upsert; all interpolated values are validated above.
        $db->query("INSERT INTO `{$table}` (`cid`,`name`,`type`,`int_value`,`str_value`,`float_value`)
            VALUES ({$cid},'{$name}','int',1,'1',0)
            ON DUPLICATE KEY UPDATE int_value=int_value+1, str_value=CAST(int_value AS CHAR), type='int'", \Typecho\Db::WRITE);
        unset(self::$counts[$cid]);
        return self::counter($cid, $name);
    }

    public static function publicPosts() {
        $db = \Typecho\Db::get();
        return $db->select()->from('table.contents')->where('type = ?', 'post')
            ->where('status = ?', 'publish')->where("password IS NULL OR password = ''")
            ->where('created <= ?', time());
    }

    public static function publicPost(int $cid): ?array {
        return \Typecho\Db::get()->fetchRow(self::publicPosts()->where('cid = ?', $cid));
    }

    public static function recentTitles(int $limit): array {
        $rows = \Typecho\Db::get()->fetchAll(self::publicPosts()->select('cid', 'title')
            ->order('created', \Typecho\Db::SORT_DESC)->limit(max(1, min(100, $limit))));
        return array_map(fn($row) => ['cid' => (int) $row['cid'], 'title' => html_entity_decode($row['title'], ENT_QUOTES, 'UTF-8')], $rows);
    }

    public static function countPublic(): int {
        return (int) \Typecho\Db::get()->fetchObject(self::publicPosts()->select(['COUNT(*)' => 'n']))->n;
    }

    public static function search(string $query): array {
        $query = mb_substr(trim($query), 0, 100, 'UTF-8');
        if ($query === '') return [];
        $pattern = '%' . str_replace(['\\','%','_'], ['\\\\','\\%','\\_'], $query) . '%';
        $db = \Typecho\Db::get();
        $rows = $db->fetchAll(self::publicPosts()->where('(title LIKE ? OR text LIKE ?)', $pattern, $pattern)
            ->order('created', \Typecho\Db::SORT_DESC)->limit(40));
        $items = [];
        foreach ($rows as $row) {
            // Use Typecho's filtered row to support custom permalink formats.
            $post = \Widget\Base\Contents::allocWithAlias('dygita-search-' . $row['cid']);
            $post->push($row);
            $text = $row['text'];
            if (str_starts_with($text, '<!--markdown-->')) $text = \Utils\Markdown::convert(substr($text, 15));
            $text = trim(html_entity_decode(strip_tags($text), ENT_QUOTES, 'UTF-8'));
            $pos = mb_stripos($text, $query, 0, 'UTF-8');
            $items[] = ['cid' => (int) $row['cid'], 'title' => html_entity_decode($row['title'], ENT_QUOTES, 'UTF-8'),
                'url' => $post->permalink, 'excerpt' => mb_substr($text, $pos === false ? 0 : max(0, $pos - 30), 180, 'UTF-8'),
                'date' => date('Y-m-d', $row['created'])];
        }
        return $items;
    }
}
