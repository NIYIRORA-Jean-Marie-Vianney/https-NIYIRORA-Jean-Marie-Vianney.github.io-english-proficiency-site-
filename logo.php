<?php $p=__DIR__.'/storage/logo.jpg';if(!is_file($p)){http_response_code(404);exit;}header('Content-Type: image/jpeg');header('Cache-Control: max-age=300');readfile($p);
