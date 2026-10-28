<?php require 'inc/bootstrap.php';need('cpd');$w=$_GET['what']??'';
if($w==='db'){audit('backup','database');header('Content-Type: application/sql; charset=utf-8');header('Content-Disposition: attachment; filename="EPMS-database-'.date('Y-m-d').'.sql"');
 echo "SET NAMES utf8mb4;\nSET FOREIGN_KEY_CHECKS=0;\n";foreach(['users','reports','files','settings','audit','review_log','notifications','classes','class_teachers'] as $t){$c=array_values(q("SHOW CREATE TABLE `$t`")->fetch());echo "DROP TABLE IF EXISTS `$t`;\n{$c[1]};\n";
  foreach(q("SELECT * FROM `$t`") as $r)echo "INSERT INTO `$t` VALUES(".implode(',',array_map(fn($v)=>$v===null?'NULL':db()->quote((string)$v),$r)).");\n";}echo "SET FOREIGN_KEY_CHECKS=1;\n";exit;}
if($w==='files'){if(!class_exists('ZipArchive')){flash('The PHP zip extension is not enabled. Copy the storage folder manually.',1);go('backup.php');}
 $tmp=tempnam(sys_get_temp_dir(),'epms');$z=new ZipArchive;$z->open($tmp,ZipArchive::OVERWRITE);foreach(glob(__DIR__.'/storage/uploads/*') as $f)if(is_file($f))$z->addFile($f,'uploads/'.basename($f));
 if(is_file(__DIR__.'/storage/logo.jpg'))$z->addFile(__DIR__.'/storage/logo.jpg','logo.jpg');$z->close();audit('backup','files');
 header('Content-Type: application/zip');header('Content-Disposition: attachment; filename="EPMS-files-'.date('Y-m-d').'.zip"');header('Content-Length: '.filesize($tmp));readfile($tmp);unlink($tmp);exit;}
top('Backup');?><h1>Backup</h1><div class="card"><p>Download both files regularly (weekly is recommended) and keep them on a flash disk or another computer.</p>
<div class="row"><a class="btn" href="backup.php?what=db">Download database backup (.sql)</a><a class="btn s" href="backup.php?what=files">Download uploaded files (.zip)</a></div>
<p class="mu">To restore: open phpMyAdmin, select the database, choose Import and upload the .sql file; then copy the zip's contents into the storage folder.</p></div><?php bot();
