<?php require 'inc/bootstrap.php';need();$x=q('SELECT f.*,r.user_id FROM files f JOIN reports r ON r.id=f.report_id WHERE f.id=?',[(int)($_GET['id']??0)])->fetch();
if(!$x||(me()['role']==='teacher'&&$x['user_id']!=me()['id']))exit('Not found.');$p=__DIR__.'/storage/uploads/'.$x['stored_name'];if(!is_file($p))exit('File missing.');
header('Content-Type: '.$x['mime']);header('Content-Length: '.filesize($p));header('Content-Disposition: attachment; filename="'.str_replace(['"','\\',"\r","\n"],'_',$x['orig_name']).'"');readfile($p);
