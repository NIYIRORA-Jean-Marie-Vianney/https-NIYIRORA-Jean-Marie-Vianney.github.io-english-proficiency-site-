<?php // System check: open before installing, or while signed in as CPD Coordinator afterwards.
require __DIR__.'/config.php';session_start();
require __DIR__.'/inc/installed.php';$inst=installed();
if($inst&&(($_SESSION['u']['role']??'')!=='cpd')){http_response_code(403);exit('Sign in as CPD Coordinator to run the system check.');}
if(isset($_GET['pdf'])){require __DIR__.'/inc/pdf.php';$p=new Pdf;if(is_file(__DIR__.'/storage/logo.jpg'))$p->logo(__DIR__.'/storage/logo.jpg');
 $p->text('PDF engine test',16,true);$p->text('Accents: é è ê à ç ï ô ù - Quotes: ’ “ ” - Long text wraps correctly: '.str_repeat('English proficiency activities. ',12));
 $p->gap(6);$p->bar('Debate',8,10);$p->bar('Reading session',5,10);$p->sig([[40,200,'CPD Coordinator'],[380,555,'Headteacher']]);
 header('Content-Type: application/pdf');header('Content-Disposition: attachment; filename="pdf-test.pdf"');echo $p->out();exit;}
$c=[];$ok=function($n,$v,$h='')use(&$c){$c[]=[$n,$v,$h];};
$ok('PHP 8.0 or newer (found '.PHP_VERSION.')',version_compare(PHP_VERSION,'8.0','>='),'Install a recent XAMPP.');
foreach(['pdo_mysql'=>'Database access','fileinfo'=>'Checking uploaded file types','mbstring'=>'Text handling in PDFs','zip'=>'File backups (optional)','gd'=>'Logo check (optional)'] as $e=>$w)$ok("Extension $e - $w",extension_loaded($e),"Open php.ini (XAMPP Control Panel > Apache > Config), remove the ; before extension=$e, restart Apache.");
$ok('Folder storage/uploads is writable',is_writable(__DIR__.'/storage/uploads'),'Give Apache write permission to the storage folder.');
$ok('Upload limit is at least 10 MB (now '.ini_get('upload_max_filesize').', post '.ini_get('post_max_size').')',(int)ini_get('upload_max_filesize')>=10&&(int)ini_get('post_max_size')>=10,'Raise upload_max_filesize and post_max_size in php.ini.');
try{new PDO('mysql:host='.DB_HOST.';charset=utf8mb4',DB_USER,DB_PASS);$ok('MySQL connection ('.DB_USER.'@'.DB_HOST.')',true);}catch(Throwable $e){$ok('MySQL connection',false,'Start MySQL in XAMPP, or fix DB_USER / DB_PASS in config.php.');}
?><!doctype html><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>System check</title><link rel="stylesheet" href="assets/style.css"><main style="max-width:720px"><div class="card"><h1>System check</h1>
<table><?php foreach($c as [$n,$v,$h])echo '<tr><td>'.($v?'✅':'❌').'</td><td>'.htmlspecialchars($n).($v?'':'<div class="mu">'.htmlspecialchars($h).'</div>').'</td></tr>';?></table>
<div class="row"><a class="btn" href="check.php?pdf=1">Download test PDF</a><?php if(!$inst)echo '<a class="btn s" href="install.php">Continue to install</a>';?></div>
<p class="mu">Open the test PDF: you should see accents, wrapped text, two bars and two signature lines. Items marked optional will not stop the system from working.</p></div></main>
