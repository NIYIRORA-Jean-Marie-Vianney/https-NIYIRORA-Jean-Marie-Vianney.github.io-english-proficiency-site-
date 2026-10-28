<?php require 'inc/bootstrap.php';if(me())go('dashboard.php');
$reg=cfg('allow_cpd_reg','1')==='1';top('Welcome');?>
<div class="hero"><?php if(is_file(__DIR__.'/storage/logo.jpg'))echo '<img class="logo" style="margin:0 auto 10px" src="logo.php" alt="">';?>
<p class="mu" style="margin:0">Welcome to</p><h1><?=h(cfg('school'))?></h1><p class="lead"><?=APP?></p>
<p class="mu">Record, review and report English-language activities across the school, in one place.</p>
<div class="row" style="justify-content:center"><a class="btn" href="login.php">Sign in</a><?php if($reg)echo '<a class="btn s" href="register.php">Register as CPD Coordinator</a>';?></div>
<p class="mu" style="margin-top:12px"><a href="forgot.php">Forgot your password?</a></p></div>
<div class="g2">
<div class="card"><h2>Teachers</h2><p class="mu">See the classes assigned to you, submit activity reports with photos or documents, and follow approvals and feedback.</p></div>
<div class="card"><h2>Headteacher, Deputy and CPD</h2><p class="mu">Approve or reject reports, give feedback, and watch which classes and teachers are active.</p></div>
<div class="card"><h2>CPD Coordinator</h2><p class="mu">Registers classes, assigns them to teachers, manages accounts and produces weekly, termly and annual reports.</p></div></div>
<?php bot();
