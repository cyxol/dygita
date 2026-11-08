<?php
require __DIR__ . '/test-bootstrap.php';
$_SERVER['REQUEST_METHOD'] = 'POST';
function preference(array $params): array {
    $request = new \Typecho\Widget\Request(\Typecho\Request::getInstance(), new \Typecho\Config($params));
    $response = new \Typecho\Widget\Response(\Typecho\Request::getInstance(), \Typecho\Response::getInstance());
    $action = new Dygita_Action_Preference($request, $response);
    ob_start(); $action->action(); return json_decode(ob_get_clean(), true);
}
check(preference(['type'=>'theme','value'=>'dark'])['error'] === 'permission', 'Guest could change defaults');
$db=\Typecho\Db::get(); $name='dygita-review-' . bin2hex(random_bytes(4));
$uid=$db->query($db->insert('table.users')->rows(['name'=>$name,'password'=>\Typecho\Common::hash('review-password'),'group'=>'administrator','authCode'=>'test']));
$old=$db->fetchRow($db->select()->from('table.options')->where('name = ?', 'dygita_theme')->where('user = ?', 0));
try {
    check(\Widget\User::alloc()->login($name, 'review-password', true), 'Test login failed');
    check(preference(['type'=>'theme','value'=>'dark','_'=>'bad'])['error'] === 'token', 'Bad CSRF accepted');
    $token=\Widget\Security::alloc()->getToken('dygita-preference');
    check(preference(['type'=>'headerColor','value'=>'invalid','_'=>$token])['error'] === 'value', 'Invalid color accepted');
    check(preference(['type'=>'theme','value'=>'dark','_'=>$token])['ok'] === true, 'Valid preference rejected');
    check($db->fetchRow($db->select('value')->from('table.options')->where('name = ?', 'dygita_theme')->where('user = ?', 0))['value'] === 'dark', 'Preference not persisted');
    echo "PASS: guest rejected, CSRF checked, input validated, administrator preference saved\n";
} finally {
    $db->query($db->delete('table.users')->where('uid = ?', $uid));
    $db->query($db->delete('table.options')->where('name = ?', 'dygita_theme')->where('user = ?', 0));
    if ($old) $db->query($db->insert('table.options')->rows($old));
}
