<?php require 'inc/bootstrap.php';if(me())go('dashboard.php');
// Password recovery for the CPD Coordinator using the recovery code made at registration (or in My account).
$err='';$done=null;
if($_SERVER['REQUEST_METHOD']==='POST'){chk();$un=strtolower(trim($_POST['u']??''));$pw=$_POST['p']??'';
 if(ipBlocked())$err='Too many failed attempts from this device. Wait 15 minutes and try again.';
 elseif(!strong($pw)||$pw!==($_POST['p2']??''))$err='New password needs 8+ characters with letters and numbers, and must match the confirmation.';
 else{$u=q("SELECT * FROM users WHERE username=? AND role='cpd' AND active=1",[$un])->fetch();
  if($u&&$u['recovery_hash']&&sameSchool($_POST['s']??'')&&password_verify(recNorm($_POST['c']??''),$u['recovery_hash'])){
   q('UPDATE users SET pass_hash=?,must_change=0,fails=0,locked_until=NULL WHERE id=?',[password_hash($pw,PASSWORD_DEFAULT),$u['id']]);
   $code=recSet($u['id']);audit('password_recovered',$un);$done=$code;}
  else{ipFail();audit('recovery_failed',$un);$err='The details do not match. Check the username, school name and recovery code.';}}}
top('Recover password');?><div class="card auth"><h1>Recover CPD Coordinator password</h1>
<?php if($done):?><div class="ok">Password changed.</div><h2>Your new recovery code</h2><p class="mu">The old code no longer works. Write the new one down; it is shown only once.</p>
<p style="font-size:20px;font-weight:700;letter-spacing:1px;text-align:center;background:var(--bg);padding:10px;border-radius:6px"><?=h($done)?></p>
<div class="row np"><button class="s" onclick="window.print()">Print</button><a class="btn" href="login.php">Go to sign in</a></div>
<?php else:?><p class="mu">Enter your username, the school name and the recovery code you received when you registered.</p>
<?php if($err)echo '<div class="er" role="alert">'.h($err).'</div>';?>
<form method="post"><input type="hidden" name="t" value="<?=csrf()?>"><label>Username</label><input name="u" required autocapitalize="none" value="<?=h($_POST['u']??'')?>"><label>School name</label><input name="s" required value="<?=h($_POST['s']??'')?>">
<label>Recovery code</label><input name="c" required autocomplete="off" placeholder="XXXX-XXXX-XXXX-XXXX" value="<?=h($_POST['c']??'')?>">
<label>New password (8+ characters, letters and numbers)</label><input type="password" name="p" required data-strength autocomplete="new-password"><label>Confirm new password</label><input type="password" name="p2" required autocomplete="new-password"><br><button style="width:100%">Reset password</button></form>
<p class="mu tip">No recovery code? Another CPD Coordinator can reset your password in Accounts. Teachers, Headteacher and Deputy: ask the CPD Coordinator to reset yours.<br><a href="login.php">Back to sign in</a></p><?php endif;?></div><?php bot();
