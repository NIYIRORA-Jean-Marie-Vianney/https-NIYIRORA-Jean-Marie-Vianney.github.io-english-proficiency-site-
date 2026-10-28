<?php require 'inc/bootstrap.php';need('cpd');
if($_SERVER['REQUEST_METHOD']==='POST'){chk();foreach(['school','year','t1s','t1e','t2s','t2e','t3s','t3e','subjects'] as $k)q('REPLACE INTO settings VALUES(?,?)',[$k,trim($_POST[$k]??'')]);q('REPLACE INTO settings VALUES(?,?)',['allow_cpd_reg',isset($_POST['allow_cpd_reg'])?'1':'0']);
 $bad=0;$f=$_FILES['logo']??null;if($f&&$f['error']===UPLOAD_ERR_OK){if((new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name'])==='image/jpeg'&&$f['size']<1500000&&($i=getimagesize($f['tmp_name']))&&($i['channels']??3)<4)move_uploaded_file($f['tmp_name'],__DIR__.'/storage/logo.jpg');else{$bad=1;flash('Logo must be a JPG (RGB or grey) under 1.5 MB. Other settings were saved.',1);}}
 audit('settings_saved');if(!$bad)flash('Settings saved.');go('settings.php');}
top('Settings');$T=terms();?><h1>School settings</h1><div class="card"><form method="post" enctype="multipart/form-data"><input type="hidden" name="t" value="<?=csrf()?>">
<label>School name (appears on PDF reports)</label><input name="school" value="<?=h(cfg('school'))?>" required>
<label>School logo (JPG, optional - printed on PDF reports and the sign-in page)</label><input type="file" name="logo" accept=".jpg,.jpeg">
<label>Academic year starts in (e.g. 2026)</label><input name="year" type="number" value="<?=h(cfg('year',substr($T[1][0],0,4)))?>">
<div class="g"><?php foreach($T as $n=>$r):?><div><label>Term <?=$n?> start</label><input type="date" name="t<?=$n?>s" value="<?=$r[0]?>"></div><div><label>Term <?=$n?> end</label><input type="date" name="t<?=$n?>e" value="<?=$r[1]?>"></div><?php endforeach;?></div>
<div class="g2"><div><label>Classes</label><p class="mu">Classes are now registered and assigned to teachers on the <a href="classes.php">Classes</a> page.</p></div><div><label>Subjects (one per line)</label><textarea name="subjects"><?=h(cfg('subjects'))?></textarea></div></div>
<label class="ck" style="margin-top:10px"><input type="checkbox" name="allow_cpd_reg" value="1"<?=cfg('allow_cpd_reg','1')==='1'?' checked':''?>> Allow CPD Coordinators to register on the Welcome page (needs the exact school name; a new registration must be approved by an existing CPD Coordinator)</label>
<p class="mu">Set the term dates from the official school calendar; termly and annual reports use them.</p><button>Save settings</button></form></div><?php bot();
