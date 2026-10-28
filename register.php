<?php require 'inc/bootstrap.php';if(me())go('dashboard.php');
// Self-registration of a CPD Coordinator. The person must give the school name already stored in the system.
$open=cfg('allow_cpd_reg','1')==='1';$err='';$done=null;
if($open&&$_SERVER['REQUEST_METHOD']==='POST'){chk();
 $nm=trim($_POST['n']??'');$un=strtolower(trim($_POST['u']??''));$pw=$_POST['p']??'';
 if(ipBlocked())$err='Too many failed attempts from this device. Wait 15 minutes and try again.';
 elseif(!sameSchool($_POST['s']??'')){ipFail();audit('cpd_register_failed','wrong school name');$err='That school name does not match the school registered in this system. Type it exactly as it appears on the Welcome page.';}
 elseif($nm===''||strlen($nm)>120||!preg_match('/^[a-z0-9._-]{3,30}$/',$un))$err='Enter your full name and a username of 3 to 30 letters, numbers, dots, dashes or underscores.';
 elseif(!strong($pw)||$pw!==($_POST['p2']??''))$err='Password needs 8+ characters with letters and numbers, and must match the confirmation.';
 elseif(q('SELECT 1 FROM users WHERE username=?',[$un])->fetch())$err='That username is already taken. Choose another.';
 elseif((int)q('SELECT COUNT(*) FROM users WHERE pending=1')->fetchColumn()>=5)$err='Too many registrations are already waiting for approval. Ask the current CPD Coordinator to review them.';
 else{$has=(bool)q("SELECT 1 FROM users WHERE role='cpd' AND active=1 AND pending=0")->fetch();
  q("INSERT INTO users(name,username,role,pass_hash,must_change,active,pending) VALUES(?,?,'cpd',?,0,?,?)",[$nm,$un,password_hash($pw,PASSWORD_DEFAULT),$has?0:1,$has?1:0]);
  $id=(int)db()->lastInsertId();$code=recSet($id);audit('cpd_registered',"$un".($has?' (pending approval)':''));
  if($has)foreach(q("SELECT id FROM users WHERE role='cpd' AND active=1")->fetchAll() as $x)notify($x['id'],null,'New CPD Coordinator registration waiting for approval: '.$nm.' ('.$un.')');
  $done=['code'=>$code,'pending'=>$has,'un'=>$un];}}
top('Register');?><div class="card auth"><h1>Register as CPD Coordinator</h1>
<?php if($done):?><div class="ok">Registration received.</div>
<?php if($done['pending'])echo '<div class="wa">An active CPD Coordinator already exists, so your account must be <b>approved by them</b> (Accounts page) before you can sign in.</div>';else echo '<div class="ok">You can sign in now with the username <b>'.h($done['un']).'</b>.</div>';?>
<h2>Your recovery code</h2><p class="mu">Write it down and keep it safe. It is the only way to recover your own password, and it is shown only once.</p>
<p style="font-size:20px;font-weight:700;letter-spacing:1px;text-align:center;background:var(--bg);padding:10px;border-radius:6px"><?=h($done['code'])?></p>
<div class="row np"><button class="s" onclick="window.print()">Print</button><a class="btn" href="login.php">Go to sign in</a></div>
<?php elseif(!$open):?><div class="wa">CPD Coordinator registration is switched off. Ask the current CPD Coordinator to create or enable your account.</div><p><a href="index.php">Back to Welcome</a></p>
<?php else:?><p class="mu">Only the school's CPD Coordinator should register here. You must type the school name exactly as it is registered in this system.</p>
<?php if($err)echo '<div class="er" role="alert">'.h($err).'</div>';?>
<form method="post"><input type="hidden" name="t" value="<?=csrf()?>"><label>School name</label><input name="s" required value="<?=h($_POST['s']??'')?>"><label>Your full name</label><input name="n" required value="<?=h($_POST['n']??'')?>"><label>Username</label><input name="u" required autocapitalize="none" value="<?=h($_POST['u']??'')?>">
<label>Password (8+ characters, letters and numbers)</label><input type="password" name="p" required data-strength autocomplete="new-password"><label>Confirm password</label><input type="password" name="p2" required autocomplete="new-password"><br><button style="width:100%">Register</button></form>
<p class="mu tip">Already registered? <a href="login.php">Sign in</a> · <a href="index.php">Welcome page</a></p><?php endif;?></div><?php bot();
