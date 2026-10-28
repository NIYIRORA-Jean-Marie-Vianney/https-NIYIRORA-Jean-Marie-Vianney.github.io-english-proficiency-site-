<?php require 'inc/bootstrap.php';need();$u=me();
$rows=q('SELECT * FROM notifications WHERE user_id=? ORDER BY id DESC LIMIT 60',[$u['id']])->fetchAll();
top('Notifications');?><h1>Notifications</h1>
<div class="card"><?php foreach($rows as $x)echo '<div class="nt'.($x['is_read']?'':' un').'"><div>'.($x['report_id']?'<a href="report_view.php?id='.(int)$x['report_id'].'">'.h($x['message']).'</a>':h($x['message'])).'<div class="mu">'.h($x['ts']).'</div></div>'.($x['is_read']?'':'<span class="tag submitted">New</span>').'</div>';
if(!$rows)echo '<div class="empty">Nothing yet. You will be notified here when a report is submitted for review, approved or rejected.</div>';?></div><?php bot();
q('UPDATE notifications SET is_read=1 WHERE user_id=? AND is_read=0',[$u['id']]);
