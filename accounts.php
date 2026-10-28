<?php require 'inc/bootstrap.php';need('cpd');
function tmp(){$c='abcdefghjkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789';$s='';for($i=0;$i<9;$i++)$s.=$c[random_int(0,strlen($c)-1)];return $s.'7';}
function auto($n){$p=preg_split('/\s+/',strtolower(trim($n)));$b=substr(preg_replace('/[^a-z0-9]/','',$p[0]).(count($p)>1?'.'.preg_replace('/[^a-z0-9]/','',end($p)):''),0,26);$u=$b;$i=1;
 while(strlen($u)<3||q('SELECT 1 FROM users WHERE username=?',[$u])->fetch())$u=$b.++$i;return $u;}
function mk($n,$u,$r){$u=strtolower(trim($u));$n=trim($n);if(!$n||!preg_match('/^[a-z0-9._-]{3,30}$/',$u)||!isset(ROLES[$r])||$r==='cpd')return null;
 if(q('SELECT 1 FROM users WHERE username=?',[$u])->fetch())return null;$p=tmp();
 q('INSERT INTO users(name,username,role,pass_hash,must_change) VALUES(?,?,?,?,1)',[$n,$u,$r,password_hash($p,PASSWORD_DEFAULT)]);audit('account_created',"$u (".ROLES[$r].')');return [$n,$u,ROLES[$r],$p];}
if($_SERVER['REQUEST_METHOD']==='POST'){chk();$a=$_POST['a']??'';
 if($a==='add'){$ok=[];$bad=[];foreach(preg_split('/\R/',$_POST['bulk']??'') as $ln){$x=array_map('trim',explode(',',$ln));if($x[0]==='')continue;
   $r=mk($x[0],($x[1]??'')!==''?$x[1]:auto($x[0]),$_POST['r']??'');$r?$ok[]=$r:$bad[]=$x[0];}
  if($ok)$_SESSION['cred']=$ok;if($bad)flash('Not created (invalid or duplicate username): '.implode(', ',$bad),1);elseif($ok)flash(count($ok).' account(s) created.');}
 else{$id=(int)($_POST['id']??0);$t=q('SELECT * FROM users WHERE id=?',[$id])->fetch();
  if($t&&$a==='save'&&$t['role']!=='cpd'&&isset(ROLES[$_POST['r']])&&$_POST['r']!=='cpd'&&trim($_POST['n'])){q('UPDATE users SET name=?,role=? WHERE id=?',[trim($_POST['n']),$_POST['r'],$id]);audit('account_edited',$t['username']);flash('Account updated.');}
  if($t&&$a==='reset'){$p=tmp();q('UPDATE users SET pass_hash=?,must_change=1,fails=0,locked_until=NULL WHERE id=?',[password_hash($p,PASSWORD_DEFAULT),$id]);$_SESSION['cred']=[[$t['name'],$t['username'],ROLES[$t['role']],$p]];audit('password_reset',$t['username']);}
  if($t&&$t['pending']&&$a==='approve'){q('UPDATE users SET active=1,pending=0 WHERE id=?',[$id]);audit('cpd_approved',$t['username']);flash($t['name'].' is approved and can now sign in as CPD Coordinator.');}
  if($t&&$t['pending']&&$a==='deny'){q('DELETE FROM users WHERE id=?',[$id]);audit('cpd_rejected',$t['username']);flash('Registration rejected and removed.');}
  if($t&&!$t['pending']&&$a==='toggle'&&$id!=me()['id']){q('UPDATE users SET active=1-active WHERE id=?',[$id]);audit('account_toggled',$t['username']);flash('Account status updated.');}}
 go('accounts.php');}
top('Accounts');$cr=$_SESSION['cred']??null;unset($_SESSION['cred']);
$pd=q('SELECT * FROM users WHERE pending=1 ORDER BY id')->fetchAll();
$us=q('SELECT u.*,(SELECT COUNT(*) FROM reports r WHERE r.user_id=u.id) nr,(SELECT GROUP_CONCAT(DISTINCT c.name ORDER BY c.name SEPARATOR \', \') FROM class_teachers ct JOIN classes c ON c.id=ct.class_id WHERE ct.user_id=u.id) cls FROM users u WHERE u.pending=0 ORDER BY FIELD(role,\'cpd\',\'head\',\'dos\',\'teacher\'),name')->fetchAll();?>
<h1>Accounts</h1>
<?php if($cr):?><div class="card"><h2>New login details - shown once</h2><p class="mu">Print this page and hand each person their details. They must change the password at first sign-in. System address: <?=h(baseUrl().'login.php')?></p>
<table><tr><th>Name</th><th>Username</th><th>Role</th><th>Temporary password</th></tr><?php foreach($cr as $c)echo '<tr><td>'.h($c[0]).'</td><td>'.h($c[1]).'</td><td>'.h($c[2]).'</td><td><b>'.h($c[3]).'</b></td></tr>';?></table><div class="row np"><button class="s" onclick="window.print()">Print</button></div></div><?php endif;?>
<?php if($pd):?><div class="card np"><h2>CPD Coordinator registrations waiting for approval (<?=count($pd)?>)</h2><p class="mu">These people registered on the Welcome page using the school name. Approve only people you recognise.</p><table><tr><th>Name</th><th>Username</th><th>Registered</th><th></th></tr>
<?php foreach($pd as $x)echo '<tr><td>'.h($x['name']).'</td><td>'.h($x['username']).'</td><td class="mu">'.h($x['created_at']).'</td><td><form method="post" class="rv"><input type="hidden" name="t" value="'.csrf().'"><input type="hidden" name="id" value="'.$x['id'].'"><button class="gr sm" name="a" value="approve">Approve</button><button class="d sm" name="a" value="deny" onclick="return confirm(\'Reject and remove this registration?\')">Reject</button></form></td></tr>';?></table></div><?php endif;?>
<div class="card np"><h2>Add accounts</h2><form method="post"><input type="hidden" name="t" value="<?=csrf()?>"><input type="hidden" name="a" value="add">
<label>One person per line: Full name, username (username is optional and is generated from the name if left out)</label><textarea name="bulk" placeholder="Jean Uwimana, jeanu&#10;Marie Mukamana" required></textarea>
<label>Role for these accounts</label><select name="r"><option value="teacher">Teacher</option><option value="head">Headteacher</option><option value="dos">Deputy Headteacher (Studies)</option></select><br><br><button>Create accounts</button></form></div>
<div class="card sc np"><table><tr><th>Name</th><th>Role</th><th>Username</th><th>Classes</th><th>Reports</th><th>Last sign-in</th><th></th></tr>
<?php foreach($us as $u):$cp=$u['role']==='cpd';$fid='f'.$u['id'];?><tr>
<td><form id="<?=$fid?>" method="post"><input type="hidden" name="t" value="<?=csrf()?>"><input type="hidden" name="id" value="<?=$u['id']?>"></form><input form="<?=$fid?>" name="n" value="<?=h($u['name'])?>" <?=$cp?'readonly':''?>></td>
<td><?php if($cp)echo h(ROLES['cpd']);else{echo '<select form="'.$fid.'" name="r">';foreach(ROLES as $k=>$v)if($k!=='cpd')echo '<option value="'.$k.'"'.($u['role']===$k?' selected':'').'>'.h($v).'</option>';echo '</select>';}?></td>
<td><?=h($u['username'])?><?=$u['active']?'':' <span class="tag">disabled</span>'?></td><td class="mu"><?=h($u['cls']?:'-')?></td><td><?=$u['nr']?></td><td class="mu"><?=h($u['last_login']?:'never')?></td><td style="white-space:nowrap">
<?php if(!$cp)echo '<button form="'.$fid.'" class="s" name="a" value="save">Save</button> ';?><button form="<?=$fid?>" class="s" name="a" value="reset" onclick="return confirm('Reset this password?')">Reset password</button>
<?php if($u['id']!=me()['id'])echo ' <button form="'.$fid.'" class="s" name="a" value="toggle">'.($u['active']?'Disable':'Enable').'</button>';?></td></tr>
<?php endforeach;?></table></div><?php bot();
