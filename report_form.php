<?php require 'inc/bootstrap.php';need('teacher','cpd');$u=me();$id=(int)($_GET['id']??0);$r=null;
if($id){$r=q('SELECT * FROM reports WHERE id=?',[$id])->fetch();if(!$r||($u['role']!=='cpd'&&($r['user_id']!=$u['id']||$r['status']==='reviewed')))exit('You cannot edit this report.');}
const OK=['pdf'=>['application/pdf'],'jpg'=>['image/jpeg'],'jpeg'=>['image/jpeg'],'doc'=>['application/msword','application/x-ole-storage','application/CDFV2','application/vnd.ms-office','application/octet-stream'],'docx'=>['application/vnd.openxmlformats-officedocument.wordprocessingml.document','application/zip','application/vnd.ms-office','application/octet-stream']];
if($_SERVER['REQUEST_METHOD']==='POST'){chk();$d=$_POST['d']??'';$desc=trim($_POST['desc']??'');$err='';$b=max(0,min(5000,(int)$_POST['b']));$g=max(0,min(5000,(int)$_POST['g']));
 $sk=implode(',',array_values(array_intersect(SKILLS,$_POST['sk']??[])));
 if(!$d||$d>date('Y-m-d')||!$desc||!in_array($_POST['type']??'',TYPES)||$b+$g<1)$err='Enter a valid date (not in the future), activity type, at least one participating student, and a description.';
 $cid=(string)($_POST['cid']??'');$cn='Mixed / whole school';$ci=null;
 if(!$err){if($cid==='keep'&&$r){$cn=$r['class_name'];$ci=$r['class_id'];}
  elseif($cid!==''&&ctype_digit($cid)){$k=q('SELECT * FROM classes WHERE id=?',[(int)$cid])->fetch();
   $okc=$k&&($u['role']==='cpd'||($r&&$r['class_id']==$k['id'])||($k['active']&&q('SELECT 1 FROM class_teachers WHERE class_id=? AND user_id=?',[$k['id'],$u['id']])->fetch()));
   if(!$okc)$err='Choose one of the classes assigned to you. Ask the CPD Coordinator if a class is missing.';
   else{$cn=$k['name'];$ci=(int)$k['id'];$en=$k['boys']+$k['girls'];if($en>0&&$b+$g>$en)$err='You entered '.($b+$g).' participants, but class '.$k['name'].' has '.$en.' registered students.';}}
  elseif($cid!=='')$err='Invalid class.';}
 $fi=new finfo(FILEINFO_MIME_TYPE);$ups=[];
 if(!$err&&!empty($_FILES['f']['name'][0]))foreach($_FILES['f']['name'] as $i=>$nm){if($_FILES['f']['error'][$i]!==UPLOAD_ERR_OK){$err="Could not upload $nm.";break;}
  $ext=strtolower(pathinfo($nm,PATHINFO_EXTENSION));$tmp=$_FILES['f']['tmp_name'][$i];
  if(!isset(OK[$ext])||!in_array($fi->file($tmp),OK[$ext])){$err="$nm: only PDF, Word and JPG files are accepted.";break;}
  if($_FILES['f']['size'][$i]>10*1024*1024){$err="$nm is larger than 10 MB.";break;}$ups[]=[$nm,$tmp,$ext,$fi->file($tmp),$_FILES['f']['size'][$i]];}
 if($err){flash($err,1);go($id?"report_form.php?id=$id":'report_form.php');}
 $v=[$d,$cn,$ci,trim($_POST['s']),$_POST['type'],$sk,$b,$g,$b+$g,$desc,trim($_POST['o'])];
 if($id){q('UPDATE reports SET activity_date=?,class_name=?,class_id=?,subject=?,type=?,skills=?,boys=?,girls=?,participants=?,description=?,outcome=?'.($u['role']==='cpd'?'':",status='submitted',reviewed_at=NULL").' WHERE id=?',[...$v,$id]);
  foreach($_POST['del']??[] as $fid){$x=q('SELECT * FROM files WHERE id=? AND report_id=?',[(int)$fid,$id])->fetch();if($x){@unlink(__DIR__.'/storage/uploads/'.$x['stored_name']);q('DELETE FROM files WHERE id=?',[$x['id']]);}}audit('report_edited',"#$id");if($u['role']!=='cpd'){logReview($id,'resubmitted');notifyReviewers($id,'Corrected report resubmitted by '.$u['name'].': '.$_POST['type'].', '.$d);}}
 else{q('INSERT INTO reports(user_id,activity_date,class_name,class_id,subject,type,skills,boys,girls,participants,description,outcome) VALUES(?,?,?,?,?,?,?,?,?,?,?,?)',[$u['id'],...$v]);$id=db()->lastInsertId();audit('report_created',"#$id");logReview($id,'submitted');notifyReviewers($id,'New report submitted by '.$u['name'].': '.$_POST['type'].', '.$d);}
 foreach($ups as [$nm,$tmp,$ext,$mi,$sz]){$sn=bin2hex(random_bytes(16)).'.'.$ext;move_uploaded_file($tmp,__DIR__.'/storage/uploads/'.$sn);q('INSERT INTO files(report_id,orig_name,stored_name,mime,size) VALUES(?,?,?,?,?)',[$id,basename($nm),$sn,$mi,$sz]);}
 flash('Report saved and sent for review.');go("report_view.php?id=$id");}
top($r?'Edit report':'New report');$g=fn($k,$d='')=>h($r[$k]??$d);$mysk=explode(',',$r['skills']??'');
$cls=$u['role']==='cpd'?q("SELECT c.*,NULL subj FROM classes c WHERE c.active=1 ORDER BY c.name")->fetchAll():myClasses($u['id']);
$curc=$r?(int)$r['class_id']:(int)($_GET['cid']??0);if($r&&$curc&&!in_array($curc,array_column($cls,'id')))if($x=q('SELECT c.*,NULL subj FROM classes c WHERE c.id=?',[$curc])->fetch())$cls[]=$x;
$legacy=$r&&!$r['class_id']&&$r['class_name']!==''&&$r['class_name']!=='Mixed / whole school';$presub='';foreach($cls as $k)if($k['id']==$curc&&!$r)$presub=explode(', ',(string)($k['subj']??''))[0];
$dl=function($id,$k){echo '<datalist id="'.$id.'">';foreach(lst($k) as $x)echo '<option value="'.h($x).'">';echo '</datalist>';};?>
<h1><?=$r?'Edit report':'New English activity report'?></h1><?php if($u['role']==='teacher'&&!$cls)echo '<div class="wa">No class has been assigned to you yet, so you can only report mixed or whole-school activities. Ask the CPD Coordinator to assign your classes.</div>';?><?php if($r&&$r['status']==='rejected')echo '<div class="er"><b>Rejected:</b> '.nl2br(h($r['feedback'])).'</div>';?>
<div class="card"><form method="post" enctype="multipart/form-data"><input type="hidden" name="t" value="<?=csrf()?>"><div class="g">
<div><label>Date of activity</label><input type="date" name="d" max="<?=date('Y-m-d')?>" value="<?=$g('activity_date',date('Y-m-d'))?>" required></div>
<div><label>Class</label><select name="cid" id="cid"><option value="">Mixed / whole school (no single class)</option><?php foreach($cls as $k){$en=$k['boys']+$k['girls'];echo '<option value="'.$k['id'].'" data-sub="'.h(explode(', ',(string)($k['subj']??''))[0]).'"'.($k['id']==$curc?' selected':'').'>'.h($k['name']).($en?' ('.$en.' students)':'').'</option>';}if($legacy)echo '<option value="keep" selected>'.h($r['class_name']).' (as recorded)</option>';?></select></div><div><label>Subject</label><input name="s" id="subj" list="sj" value="<?=$g('subject',$presub)?>"></div>
<div><label>Activity type</label><select name="type"><?php foreach(TYPES as $t)echo '<option'.(($r['type']??'')===$t?' selected':'').'>'.h($t).'</option>';?></select></div>
<div><label>Girls participating</label><input type="number" min="0" name="g" value="<?=$g('girls','0')?>"></div><div><label>Boys participating</label><input type="number" min="0" name="b" value="<?=$g('boys','0')?>"></div></div>
<?php $dl('sj','subjects');?><label>Language skills practised</label><div><?php foreach(SKILLS as $s)echo '<label class="ck"><input type="checkbox" name="sk[]" value="'.$s.'"'.(in_array($s,$mysk)?' checked':'').'> '.$s.'</label>';?></div>
<label>Description of the activity</label><textarea name="desc" required><?=$g('description')?></textarea><label>Outcome, observed improvement, challenges</label><textarea name="o"><?=$g('outcome')?></textarea>
<?php if($id)foreach(q('SELECT * FROM files WHERE report_id=?',[$id]) as $x)echo '<label class="ck"><input type="checkbox" name="del[]" value="'.$x['id'].'"> Remove '.h($x['orig_name']).'</label><br>';?>
<label>Attach files (PDF, Word, JPG; max 10 MB each)</label><input type="file" name="f[]" multiple accept=".pdf,.doc,.docx,.jpg,.jpeg"><br><br><button>Save and submit</button> <a class="btn s" href="reports.php">Cancel</a></form></div><?php bot();
