<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;
class Dygita_Action_Search_Index extends \Typecho\Widget implements \Widget\ActionInterface {
    public function execute() {}
    public function action() {
        $this->response->setContentType('application/json');
        $this->response->setHeader('Cache-Control', 'no-store');
        if (!$this->request->isGet()) { $this->response->setStatus(405); echo '[]'; return; }
        echo json_encode(Dygita_Repository::search((string) $this->request->get('q', '')),
            JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_INVALID_UTF8_SUBSTITUTE);
    }
}

