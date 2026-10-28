<?php require 'inc/bootstrap.php';need();$u=me();$id=(int)($_GET['id']??0);
$r=q('SELECT r.*,u.name teacher,v.name rev FROM reports r JOIN users u ON u.id=r.user_id LEFT JOIN users v ON v.id=r.reviewed_by WHERE r.id=?',[$id])->fetch();
if(!$r||($u['role']==='teacher'&&$r['user_id']!=$u['id']))exit('Not found.');
$own=$r['user_id']==$u['id'];$canEdit=$u['role']==='cpd'||($own&&$u['role']==='teacher'&&$r['status']!=='reviewed');$rv=isReviewer()&&!$own;
if($_SERVER['REQUEST_METHOD']==='POST'){chk();$a=$_POST['a']??'';
 if(in_array($a,['approve','reject'],true)&&isReviewer()){[$ok,$m]=decide($id,$a,$_POST['fb']??'');flash($m,!$ok);go("report_view.php?id=$id");}
 if($a==='del'&&$canEdit){foreach(q('SELECT stored_name FROM files WHERE report_id=?',[$id]) as $x)@unlink(__DIR__.'/storage/uploads/'.$x['stored_name']);
  q('DELETE FROM review_log WHERE report_id=?',[$id]);q('DELETE FROM notifications WHERE report_id=?',[$id]);q('DELETE FROM reports WHERE id=?',[$id]);audit('report_deleted',"#$id by {$r['teacher']}");flash('Report deleted.');go('reports.php');}
 go("report_view.php?id=$id");}
$LOG=['submitted'=>'Submitted','resubmitted'=>'Corrected and resubmitted','approved'=>'Approved','rejected'=>'Rejected'];
top('Report');?><div class="pp"><b><?=h(cfg('school'))?></b> - <?=APP?></div>
<h1><?=h($r['type'])?> <?=stag($r['status'])?></h1>
<?php if($r['status']==='rejected'&&$own)echo '<div class="er"><b>Rejected'.($r['rev']?' by '.h($r['rev']):'').':</b> '.nl2br(h($r['feedback'])).'<br><span class="mu">Correct the report and save it again. It will go back for review automatically.</span></div>';
if($own&&isReviewer())echo '<div class="wa np">This is your own report, so another reviewer (Headteacher, Deputy or CPD Coordinator) must approve or reject it.</div>';?>
<div class="card"><p class="mu"><?=h($r['activity_date'])?> · <?=h($r['teacher'])?> · Class <?=h($r['class_name']?:'-')?> · <?=h($r['subject']?:'-')?> · <?=$r['participants']?> students<?=($r['boys']+$r['girls'])?" ({$r['girls']} girls, {$r['boys']} boys)":''?></p>
<?php if($r['skills'])echo '<p>Skills: '.h(str_replace(',',', ',$r['skills'])).'</p>';?><h2>Description</h2><p><?=nl2br(h($r['description']))?></p><?php if($r['outcome'])echo '<h2>Outcome</h2><p>'.nl2br(h($r['outcome'])).'</p>';
$fs=q('SELECT * FROM files WHERE report_id=?',[$id])->fetchAll();if($fs){echo '<h2>Attachments</h2>';foreach($fs as $x)echo '<p><a href="download.php?id='.$x['id'].'">📎 '.h($x['orig_name']).'</a> <span class="mu">('.round($x['size']/1024).' KB)</span></p>';}
if($r['feedback']&&!($r['status']==='rejected'&&$own))echo '<h2>'.($r['status']==='submitted'?'Previous reviewer comment (before correction)':'Reviewer comment').'</h2><p>'.nl2br(h($r['feedback'])).'</p><p class="mu">By '.h($r['rev']).', '.h($r['reviewed_at']).'</p>';?>
<div class="row np"><?php if($canEdit)echo '<a class="btn s" href="report_form.php?id='.$id.'">Edit</a>';?><button class="s" onclick="window.print()">Print</button></div></div>
<?php if($rv):?><div class="card np"><h2>Review this report</h2><form method="post"><input type="hidden" name="t" value="<?=csrf()?>"><textarea name="fb" placeholder="Comment for the teacher (required when rejecting, optional when approving)"><?=$r['status']==='submitted'?'':h($r['feedback'])?></textarea>
<div class="row"><button class="gr" name="a" value="approve">Approve</button><button class="d" name="a" value="reject">Reject</button></div>
<?php if($r['status']!=='submitted')echo '<p class="mu">This report was already '.h(strtolower(STATUS[$r['status']])).'. Pressing a button above changes the decision and notifies the teacher.</p>';?></form></div><?php endif;?>
<?php $lg=q('SELECT * FROM review_log WHERE report_id=? ORDER BY id',[$id])->fetchAll();if($lg):?><div class="card"><h2>History</h2><ul class="tl"><?php foreach($lg as $x)echo '<li class="'.h($x['action']).'"><b>'.h($LOG[$x['action']]??$x['action']).'</b> by '.h($x['user_name']).' <span class="mu">'.h($x['ts']).'</span>'.($x['note']&&in_array($x['action'],['approved','rejected'])?'<div>'.nl2br(h($x['note'])).'</div>':'').'</li>';?></ul></div><?php endif;?>
<?php if($canEdit):?><form method="post" class="np" onsubmit="return confirm('Delete this report and its files permanently?')"><input type="hidden" name="t" value="<?=csrf()?>"><input type="hidden" name="a" value="del"><button class="d">Delete report</button></form><?php endif;bot();
