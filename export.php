<?php require 'inc/bootstrap.php';need('cpd','head','dos');
[$from,$to,$label]=period($_GET['p']??'week',$_GET['from']??'',$_GET['to']??'');$uid=(int)($_GET['uid']??0)?:null;$cls=trim($_GET['cls']??'');
$rows=array_reverse(reports(['from'=>$from,'to'=>$to,'uid'=>$uid,'cls'=>$cls,'type'=>$_GET['type']??'','status'=>$_GET['status']??'','skill'=>$_GET['skill']??'','q'=>$_GET['q']??'']));
$fmt=($_GET['fmt']??'pdf')==='csv'?'csv':'pdf';$nm='EPMS-report-'.$from.'_to_'.$to;audit('export',"$fmt $from to $to");
if($fmt==='csv'){header('Content-Type: text/csv; charset=utf-8');header("Content-Disposition: attachment; filename=\"$nm.csv\"");echo "\xEF\xBB\xBF";$o=fopen('php://output','w');
 fputcsv($o,['Date','Teacher','Class','Subject','Activity type','Skills','Girls','Boys','Participants','Description','Outcome','Status','Reviewer comment','Reviewed by','Reviewed on']);
 foreach($rows as $r)fputcsv($o,[$r['activity_date'],$r['teacher'],$r['class_name'],$r['subject'],$r['type'],$r['skills'],$r['girls'],$r['boys'],$r['participants'],$r['description'],$r['outcome'],STATUS[$r['status']]??$r['status'],$r['feedback'],$r['reviewer'],$r['reviewed_at']]);exit;}
require 'inc/pdf.php';$p=new Pdf;$G=['type'=>[],'skill'=>[],'class'=>[],'teacher'=>[],'month'=>[]];$pt=$bo=$gi=0;$te=[];
$add=function($k,$s,$r)use(&$G){$a=&$G[$k][$s];$a['n']=($a['n']??0)+1;$a['p']=($a['p']??0)+$r['participants'];$a['g']=($a['g']??0)+$r['girls'];$a['b']=($a['b']??0)+$r['boys'];};
foreach($rows as $r){$add('type',$r['type'],$r);foreach(array_filter(explode(',',$r['skills'])) as $s)$add('skill',$s,$r);$add('class',$r['class_name']?:'(no class)',$r);$add('teacher',$r['teacher'],$r);$add('month',substr($r['activity_date'],0,7),$r);$pt+=$r['participants'];$bo+=$r['boys'];$gi+=$r['girls'];$te[$r['teacher']]=1;}
ksort($G['month']);if(is_file(__DIR__.'/storage/logo.jpg'))$p->logo(__DIR__.'/storage/logo.jpg');
$p->text(cfg('school','School'),17,true);$p->text(APP,9);$p->text('English Proficiency Activities Report',13,true);$p->text("$label   ($from to $to)");
$sc=[];if($uid&&$rows)$sc[]='Teacher: '.$rows[0]['teacher'];if($cls)$sc[]="Class: $cls";if($sc)$p->text('Scope - '.implode(' | ',$sc),9);
$p->text('Prepared by '.me()['name'].' ('.ROLES[me()['role']].') on '.date('d M Y H:i'),9);$p->gap(4);$p->hr();
$p->text('Summary',12,true);$p->text('Activities: '.count($rows).'     Participations: '.$pt.' (girls '.$gi.', boys '.$bo.')     Teachers reporting: '.count($te).'     Approved: '.count(array_filter($rows,fn($r)=>$r['status']==='reviewed')));$p->gap(5);
if($G['type']){$p->text('Activities by type',11,true);$mx=max(array_column($G['type'],'n'));foreach($G['type'] as $k=>$v)$p->bar($k,$v['n'],$mx);$p->gap(6);}
foreach(['type'=>'By activity type','skill'=>'By language skill','class'=>'By class','teacher'=>'By teacher','month'=>'By month'] as $k=>$h){if(!$G[$k])continue;
 $p->text($h,11,true);$p->row(['Name','Activities','Girls','Boys','Total'],[40,290,365,425,485],true);$p->hr();foreach($G[$k] as $n=>$v)$p->row([$n,$v['n'],$v['g'],$v['b'],$v['p']],[40,290,365,425,485]);$p->gap(6);}
if(!$uid){$ms=missing($from,$to);$p->text('Teachers with no report in this period ('.count($ms).')',11,true);$p->text($ms?implode(', ',$ms):'None - every active teacher reported.',9);$p->gap(6);}
$p->hr();$p->text('Detailed activities',12,true);$p->gap(3);
foreach($rows as $i=>$r){$p->need(60);$p->text(($i+1).'. '.$r['activity_date'].' - '.$r['type'].' - Class '.($r['class_name']?:'-').' - '.($r['subject']?:'-'),10,true);
 $p->text('Teacher: '.$r['teacher'].'   Participants: '.$r['participants'].' (girls '.$r['girls'].', boys '.$r['boys'].')'.($r['skills']?'   Skills: '.str_replace(',',', ',$r['skills']):''),9,false,52,500);
 $p->text($r['description'],9,false,52,500);if($r['outcome'])$p->text('Outcome: '.$r['outcome'],9,false,52,500);if($r['feedback'])$p->text('Reviewer comment: '.$r['feedback'],9,false,52,500);$p->gap(4);}
if(!$rows)$p->text('No activities were reported for this period.');
$p->sig([[40,185,'CPD Coordinator'],[205,385,'Deputy Headteacher (Studies)'],[405,555,'Headteacher']]);
header('Content-Type: application/pdf');header("Content-Disposition: attachment; filename=\"$nm.pdf\"");echo $p->out();
