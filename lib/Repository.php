<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;

/** Data access for public content and counters. No HTML or request handling. */
final class Dygita_Repository
{

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
