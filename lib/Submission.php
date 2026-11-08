<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;

final class Dygita_Submission {
    public static function sanitize(string $html): string {
        if (!class_exists('DOMDocument')) return htmlspecialchars(strip_tags($html), ENT_QUOTES, 'UTF-8');
        $doc = new \DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);
        try {
            $doc->loadHTML('<?xml encoding="UTF-8"><html><body>' . $html . '</body></html>', LIBXML_NONET);
            $body = $doc->getElementsByTagName('body')->item(0);
            $allowed = ['p','br','strong','em','ul','ol','li','blockquote','code','pre','h2','h3','h4'];
            $clean = function ($parent) use (&$clean, $allowed) {
                foreach (iterator_to_array($parent->childNodes) as $node) {
                    if ($node instanceof \DOMComment || $node instanceof \DOMProcessingInstruction) {
                        $parent->removeChild($node); continue;
                    }
                    if (!($node instanceof \DOMElement)) continue;
                    if (in_array(strtolower($node->tagName), ['script','style','iframe','object','svg','math','template'], true)) {
                        $parent->removeChild($node); continue;
                    }
                    $clean($node);
                    if (!in_array(strtolower($node->tagName), $allowed, true)) {
                        while ($node->firstChild) $parent->insertBefore($node->firstChild, $node);
                        $parent->removeChild($node);
                    } else {
                        while ($node->attributes->length) $node->removeAttributeNode($node->attributes->item(0));
                    }
                }
            };
            $clean($body); $out = '';
            foreach ($body->childNodes as $node) $out .= $doc->saveHTML($node);
            return $out;
        } finally { libxml_clear_errors(); libxml_use_internal_errors($previous); }
    }

    public static function create(string $title, string $content, string $name): int {
        $db = \Typecho\Db::get();
        $owner = $db->fetchRow($db->select('uid')->from('table.users')->where('group = ?', 'administrator')->order('uid')->limit(1));
        if (!$owner) throw new \RuntimeException('No submission reviewer configured');
        // Use the core insert/slug rules; anonymous submissions remain drafts.
        return \Widget\Base\Contents::alloc()->insert([
            'title' => $title, 'text' => self::sanitize($content) . '<p>感谢来自：' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '</p>',
            'authorId' => (int) $owner['uid'], 'type' => 'post_draft', 'status' => 'waiting',
            'allowComment' => 1, 'allowPing' => 0, 'allowFeed' => 0
        ]);
    }
}
