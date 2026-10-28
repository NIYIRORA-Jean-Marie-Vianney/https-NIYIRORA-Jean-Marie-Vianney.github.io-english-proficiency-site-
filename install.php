<?php require __DIR__.'/config.php';
$lock=__DIR__.'/storage/installed.lock';require __DIR__.'/inc/installed.php';if(installed()){header('Location: login.php');exit;}
$m='';
if($_SERVER['REQUEST_METHOD']==='POST'){
 try{$p=new PDO('mysql:host='.DB_HOST.';charset=utf8mb4',DB_USER,DB_PASS,[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
  try{$p->exec('CREATE DATABASE IF NOT EXISTS `'.DB_NAME.'` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');}catch(Throwable $e){}$p->exec('USE `'.DB_NAME.'`');
  $p->exec("CREATE TABLE IF NOT EXISTS users(id INT AUTO_INCREMENT PRIMARY KEY,name VARCHAR(120) NOT NULL,username VARCHAR(40) NOT NULL UNIQUE,role ENUM('cpd','head','dos','teacher') NOT NULL,pass_hash VARCHAR(255) NOT NULL,must_change TINYINT NOT NULL DEFAULT 1,active TINYINT NOT NULL DEFAULT 1,fails INT NOT NULL DEFAULT 0,locked_until DATETIME NULL,created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)");
  $p->exec("CREATE TABLE IF NOT EXISTS reports(id INT AUTO_INCREMENT PRIMARY KEY,user_id INT NOT NULL,activity_date DATE NOT NULL,class_name VARCHAR(40),subject VARCHAR(80),type VARCHAR(60) NOT NULL,participants INT NOT NULL DEFAULT 0,description TEXT NOT NULL,outcome TEXT,feedback TEXT,reviewed_by INT NULL,reviewed_at DATETIME NULL,created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,INDEX(activity_date),INDEX(user_id),FOREIGN KEY(user_id) REFERENCES users(id))");
  $p->exec("CREATE TABLE IF NOT EXISTS files(id INT AUTO_INCREMENT PRIMARY KEY,report_id INT NOT NULL,orig_name VARCHAR(255) NOT NULL,stored_name VARCHAR(80) NOT NULL,mime VARCHAR(100),size INT,FOREIGN KEY(report_id) REFERENCES reports(id) ON DELETE CASCADE)");
  $p->exec("CREATE TABLE IF NOT EXISTS settings(k VARCHAR(30) PRIMARY KEY,v TEXT)");
  $u=strtolower(trim($_POST['u']));$pw=$_POST['p'];if(strlen($pw)<8||!preg_match('/[A-Za-z]/',$pw)||!preg_match('/\d/',$pw)||!preg_match('/^[a-z0-9._-]{3,30}$/',$u))throw new Exception('Username: 3+ letters/numbers. Password: 8+ characters with letters and numbers.');
  $p->prepare("INSERT INTO users(name,username,role,pass_hash,must_change) VALUES(?,?,'cpd',?,0)")->execute([trim($_POST['n']),$u,password_hash($pw,PASSWORD_DEFAULT)]);
  $p->prepare("REPLACE INTO settings VALUES('school',?)")->execute([trim($_POST['s'])]);
  file_put_contents($lock,date('c'));$m='ok';
 }catch(Throwable $e){$m=$e->getMessage();}}
?><!doctype html><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Install</title><link rel="stylesheet" href="assets/style.css"><script src="assets/app.js" defer></script><main style="max-width:460px"><div class="card"><h1>Install English Proficiency Management System</h1>
<?php if($m==='ok'):?><div class="ok">Installed. Delete install.php, then <a href="login.php">sign in</a>.</div>
<?php else:if($m)echo '<div class="er">'.htmlspecialchars($m).'</div>';?>
<p class="mu">Make sure Apache and MySQL are running in XAMPP. This creates the database and your CPD Coordinator account. <a href="check.php">Run the system check first</a>.</p>
<form method="post"><label>School name</label><input name="s" required><label>Your full name</label><input name="n" required><label>Username</label><input name="u" required><label>Password (8+ characters, letters and numbers)</label><input name="p" type="password" required data-strength autocomplete="new-password"><br><br><button>Install</button></form><?php endif;?></div></main>
