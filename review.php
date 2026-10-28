<?php require 'inc/bootstrap.php';need('cpd','head','dos');
// Handles the Approve / Reject buttons shown in report lists (single report or several ticked ones).
if($_SERVER['REQUEST_METHOD']!=='POST')go('reports.php');chk();
$back=(string)($_POST['back']??'');$dest=preg_match('/^(reports|dashboard)\.php(\?[\w=&%.\-\[\]+:,]*)?$/',$back)?$back:'reports.php';
$note=trim($_POST['comment']??'');
if(isset($_POST['one'])){[$a,$id]=array_pad(explode(':',(string)$_POST['one'],2),2,'');$id=(int)$id;
 if(!in_array($a,['approve','reject'],true)||!$id)go($dest);
 if($a==='reject'&&$note===''){flash('Write the reason for rejecting this report, then press Reject.',1);go("report_view.php?id=$id");}
 [$ok,$m]=decide($id,$a,$note);flash($m,!$ok);go($dest);}
$a=$_POST['bulk']??'';$ids=array_values(array_unique(array_map('intval',(array)($_POST['ids']??[]))));
if(!in_array($a,['approve','reject'],true)||!$ids){flash('Tick at least one report, then choose Approve or Reject.',1);go($dest);}
if($a==='reject'&&$note===''){flash('A reason is required to reject reports.',1);go($dest);}
$n=0;$skip=0;
foreach($ids as $id){$r=q('SELECT status FROM reports WHERE id=?',[$id])->fetch();
 if(!$r||$r['status']!=='submitted'){$skip++;continue;}
 [$ok]=decide($id,$a,$note);$ok?$n++:$skip++;}
flash($n.' report(s) '.($a==='approve'?'approved':'rejected').($skip?" ($skip skipped: already reviewed or your own).":'.'),$n?0:1);go($dest);
