<?php require 'inc/bootstrap.php';need('cpd');
// The CPD Coordinator registers classes and assigns them (with an optional subject) to teachers.
function cname($s){return substr(trim(preg_replace('/\s+/',' ',(string)$s)),0,40);}
if($_SERVER['REQUEST_METHOD']==='POST'){chk();$a=$_POST['a']??'';
 if($a==='add'){$ok=0;$bad=[];foreach(preg_split('/\R/',$_POST['bulk']??'') as $ln){$x=array_map('trim',explode(',',$ln));$n=cname($x[0]);if($n==='')continue;
   try{q('INSERT INTO classes(name,level,girls,boys) VALUES(?,?,?,?)',[$n,substr($x[1]??'',0,40),max(0,min(500,(int)($x[2]??0))),max(0,min(500,(int)($x[3]??0)))]);$ok++;}catch(PDOException $e){$bad[]=$n;}}
  if($ok)audit('classes_added',"$ok class(es)");
  if($bad)flash('Not added (already exists): '.implode(', ',$bad),1);elseif($ok)flash($ok.' class(es) registered.');else flash('Type at least one class name.',1);}
 elseif($a==='assign'){$c=q('SELECT * FROM classes WHERE id=?',[(int)($_POST['cid']??0)])->fetch();$t=q("SELECT * FROM users WHERE id=? AND active=1 AND role IN('teacher','cpd')",[(int)($_POST['uid']??0)])->fetch();$sub=substr(trim($_POST['s']??''),0,80);
  if(!$c||!$t)flash('Choose a class and a teacher.',1);
  elseif(q('SELECT 1 FROM class_teachers WHERE class_id=? AND user_id=? AND subject=?',[$c['id'],$t['id'],$sub])->fetch())flash($t['name'].' is already assigned to '.$c['name'].($sub?" ($sub)":'').'.',1);
  else{q('INSERT INTO class_teachers(class_id,user_id,subject) VALUES(?,?,?)',[$c['id'],$t['id'],$sub]);audit('class_assigned',$c['name'].' -> '.$t['username']);notify($t['id'],null,'You have been assigned class '.$c['name'].($sub?" ($sub)":'').'. You can now report activities for it.');flash($c['name'].' assigned to '.$t['name'].'.');}}
 else{$id=(int)($_POST['id']??0);$c=q('SELECT * FROM classes WHERE id=?',[$id])->fetch();
  if($c&&$a==='save'){$n=cname($_POST['n']??'');$dup=$n===''?1:q('SELECT 1 FROM classes WHERE name=? AND id<>?',[$n,$id])->fetch();
   if($dup)flash('Enter a class name that is not used by another class.',1);
   else{q('UPDATE classes SET name=?,level=?,girls=?,boys=? WHERE id=?',[$n,substr(trim($_POST['l']??''),0,40),max(0,min(500,(int)$_POST['g'])),max(0,min(500,(int)$_POST['b'])),$id]);q('UPDATE reports SET class_name=? WHERE class_id=?',[$n,$id]);audit('class_edited',$n);flash('Class updated.');}}
  if($c&&$a==='toggle'){q('UPDATE classes SET active=1-active WHERE id=?',[$id]);audit('class_toggled',$c['name']);flash($c['active']?'Class deactivated. It no longer appears in report forms.':'Class activated.');}
  if($c&&$a==='delete'){if(q('SELECT 1 FROM reports WHERE class_id=?',[$id])->fetch())flash('This class has reports, so it cannot be deleted. Deactivate it instead.',1);
   else{q('DELETE FROM class_teachers WHERE class_id=?',[$id]);q('DELETE FROM classes WHERE id=?',[$id]);audit('class_deleted',$c['name']);flash('Class deleted.');}}
  if($c&&strpos($a,'unassign:')===0){$x=q('SELECT * FROM class_teachers WHERE id=? AND class_id=?',[(int)substr($a,9),$id])->fetch();if($x){q('DELETE FROM class_teachers WHERE id=?',[$x['id']]);audit('class_unassigned',$c['name'].' #'.$x['user_id']);flash('Teacher removed from '.$c['name'].'.');}}}
 go('classes.php');}
top('Classes');
$cs=q('SELECT c.*,(SELECT COUNT(*) FROM reports r WHERE r.class_id=c.id) nr FROM classes c ORDER BY c.active DESC,c.name')->fetchAll();
$as=[];foreach(q('SELECT ct.id,ct.class_id,ct.subject,u.name,u.active FROM class_teachers ct JOIN users u ON u.id=ct.user_id ORDER BY u.name')->fetchAll() as $x)$as[$x['class_id']][]=$x;
$tc=q("SELECT id,name,role FROM users WHERE active=1 AND role IN('teacher','cpd') ORDER BY name")->fetchAll();
$un=count(array_filter($cs,fn($c)=>$c['active']&&empty($as[$c['id']])));?>
<h1>Classes</h1>
<div class="card st"><div><b><?=count(array_filter($cs,fn($c)=>$c['active']))?></b>active classes</div><div><b><?=array_sum(array_map(fn($c)=>$c['active']?$c['boys']+$c['girls']:0,$cs))?></b>students enrolled</div><div><b><?=$un?></b>without a teacher</div></div>
<div class="g2 np"><div class="card"><h2>Register classes</h2><form method="post"><input type="hidden" name="t" value="<?=csrf()?>"><input type="hidden" name="a" value="add">
<label>One class per line: Name, Level (optional), Girls (optional), Boys (optional)</label><textarea name="bulk" placeholder="S1A, Senior 1, 22, 20&#10;S1B, Senior 1&#10;P5" required></textarea><br><br><button>Register classes</button></form></div>
<div class="card"><h2>Assign a class to a teacher</h2><?php if(!$tc||!$cs):?><p class="mu">Register at least one class and one teacher account first.</p><?php else:?><form method="post"><input type="hidden" name="t" value="<?=csrf()?>"><input type="hidden" name="a" value="assign">
<label>Class</label><select name="cid"><?php foreach($cs as $c)if($c['active'])echo '<option value="'.$c['id'].'">'.h($c['name']).'</option>';?></select>
<label>Teacher</label><select name="uid"><?php foreach($tc as $t)echo '<option value="'.$t['id'].'">'.h($t['name']).($t['role']==='cpd'?' (CPD)':'').'</option>';?></select>
<label>Subject taught (optional)</label><input name="s" list="sj" placeholder="e.g. English"><datalist id="sj"><?php foreach(lst('subjects') as $x)echo '<option value="'.h($x).'">';?></datalist>
<p class="mu">A class can have several teachers (for example one per subject). The teacher is notified.</p><button>Assign</button></form><?php endif;?></div></div>
<div class="card sc np"><table><tr><th>Class</th><th>Level</th><th>Girls</th><th>Boys</th><th>Assigned teachers</th><th>Reports</th><th></th></tr>
<?php foreach($cs as $c):$f='f'.$c['id'];?><tr<?=$c['active']?'':' style="opacity:.6"'?>>
<td><form id="<?=$f?>" method="post"><input type="hidden" name="t" value="<?=csrf()?>"><input type="hidden" name="id" value="<?=$c['id']?>"><button name="a" value="save" tabindex="-1" aria-hidden="true" style="position:absolute;left:-9999px;width:1px;height:1px;padding:0;border:0"></button></form><input form="<?=$f?>" name="n" value="<?=h($c['name'])?>" style="min-width:90px"><?=$c['active']?'':' <span class="tag">inactive</span>'?></td>
<td><input form="<?=$f?>" name="l" value="<?=h($c['level'])?>" style="min-width:90px"></td><td><input form="<?=$f?>" type="number" min="0" name="g" value="<?=$c['girls']?>" style="width:70px"></td><td><input form="<?=$f?>" type="number" min="0" name="b" value="<?=$c['boys']?>" style="width:70px"></td>
<td><?php foreach($as[$c['id']]??[] as $x)echo '<span class="tag chip">'.h($x['name']).($x['subject']?' - '.h($x['subject']):'').($x['active']?'':' (disabled)').' <button form="'.$f.'" class="x" name="a" value="unassign:'.$x['id'].'" title="Remove" aria-label="Remove '.h($x['name']).'">&times;</button></span> ';
 if(empty($as[$c['id']]))echo '<span class="tag warn">no teacher yet</span>';?></td><td><?=$c['nr']?></td>
<td style="white-space:nowrap"><button form="<?=$f?>" class="s" name="a" value="save">Save</button> <button form="<?=$f?>" class="s" name="a" value="toggle"><?=$c['active']?'Deactivate':'Activate'?></button>
<?php if(!$c['nr'])echo ' <button form="'.$f.'" class="s" name="a" value="delete" onclick="return confirm(\'Delete this class?\')">Delete</button>';?></td></tr>
<?php endforeach;if(!$cs)echo '<tr><td colspan="7" class="empty">No classes registered yet. Add your first classes above.</td></tr>';?></table></div><?php bot();
