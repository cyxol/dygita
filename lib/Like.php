<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;
class Dygita_Action_Like extends \Typecho\Widget implements \Widget\ActionInterface {
    public function execute() {}
    public function action() {
        $this->response->setContentType('text/plain');
        $this->response->setHeader('Cache-Control', 'no-store');
        if (!$this->request->isPost()) { $this->response->setStatus(405); echo 'method_not_allowed'; return; }
        $cid = filter_var($this->request->get('cid'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if (!$cid) { $this->response->setStatus(400); echo 'invalid'; return; }
        if (!Dygita_Repository::publicPost($cid)) { $this->response->setStatus(404); echo 'not_found'; return; }
        $liked = array_filter(explode(',', (string) \Typecho\Cookie::get('extend_contents_likes')));
        if (in_array((string) $cid, $liked, true)) { echo 'already_liked'; return; }
        $count = Dygita_Repository::increment($cid, 'likes');
        $liked[] = (string) $cid;
        \Typecho\Cookie::set('extend_contents_likes', implode(',', array_slice($liked, -100)));
        echo $count;
    }
}

