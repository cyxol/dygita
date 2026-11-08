<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;
class Dygita_Action_Preference extends \Typecho\Widget implements \Widget\ActionInterface {
    public function execute() {}
    public function action() {
        $this->response->setContentType('application/json');
        $this->response->setHeader('Cache-Control', 'no-store');
        if (!$this->request->isPost()) { $this->response->setStatus(405); echo '{"error":"method"}'; return; }
        if (!\Widget\User::alloc()->pass('administrator', true)) { $this->response->setStatus(403); echo '{"error":"permission"}'; return; }
        $token = \Widget\Security::alloc()->getToken('dygita-preference');
        if (!hash_equals($token, (string) $this->request->get('_', ''))) { $this->response->setStatus(403); echo '{"error":"token"}'; return; }
        $type = $this->request->get('type'); $value = $this->request->get('value');
        if (!(($type === 'theme' && in_array($value, ['light','dark'], true)) ||
            ($type === 'headerColor' && is_string($value) && preg_match('/^#[0-9a-fA-F]{6}$/D', $value)))) {
            $this->response->setStatus(400); echo '{"error":"value"}'; return;
        }
        $db = \Typecho\Db::get(); $table = $db->getPrefix() . 'options';
        // Whitelisted type and value make these SQL literals safe.
        $db->query("INSERT INTO `{$table}` (`name`,`user`,`value`) VALUES ('dygita_{$type}',0,'{$value}')
            ON DUPLICATE KEY UPDATE value=VALUES(value)", \Typecho\Db::WRITE);
        echo '{"ok":true}';
    }
}
