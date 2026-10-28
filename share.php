<?php require 'inc/bootstrap.php';need('cpd');
if($_SERVER['REQUEST_METHOD']==='POST'){chk();
 if(($_POST['a']??'')==='rm'){if(@unlink(__DIR__.'/install.php')){audit('install_deleted');flash('install.php was deleted.');}else flash('Could not delete install.php. Delete it manually from the project folder.',1);go('share.php');}
 $v=trim($_POST['u']??'');
 if($v===''||preg_match('#^https?://[\w.\-:\[\]]+(/[\w.\-/]*)?$#',$v)){q('REPLACE INTO settings VALUES(?,?)',['base_url',$v]);audit('share_link_set',$v?:'automatic');flash($v===''?'Using the automatically detected address.':'Share link saved.');}
 else flash('Enter a full address starting with http:// or https://, for example https://myschool.example.com/english-proficiency-management-system/',1);
 go('share.php');}
top('Share link');$ip=lanIp();$link=baseUrl().'login.php';$set=trim(cfg('base_url',''));
$host=preg_replace('/:\d+$/','',$_SERVER['HTTP_HOST']??'');$local=in_array($host,['localhost','127.0.0.1','::1','[::1]'],true);
$tmp=(int)q("SELECT COUNT(*) FROM users WHERE active=1 AND must_change=1")->fetchColumn();$inst=is_file(__DIR__.'/install.php');$https=isHttps();?>
<h1>Share link for phones, tablets and other computers</h1>
<div class="card"><h2>Login link</h2><input id="lk" value="<?=h($link)?>" readonly style="font-size:16px;font-weight:600">
<div class="row"><button type="button" data-copy="lk">Copy link</button><a class="btn s" target="_blank" rel="noopener" href="https://wa.me/?text=<?=h(rawurlencode(APP.' login: '.$link))?>">Share on WhatsApp</a><a class="btn s" href="<?=h($link)?>">Open</a></div>
<p class="mu"><?=$set?'This is the address you saved below.':($ip?'Detected network address of this computer: <b>'.h($ip).'</b> (works on the same Wi-Fi only).':'Could not detect this computer\'s network address. Type it below (run ipconfig and read IPv4 Address).')?>
<?=!$local&&$https&&!$set?' You are using a secure public link, so the link above works from any network.':''?></p></div>

<div class="card"><h2>A. Same Wi-Fi or network only</h2><ol>
<li>This computer is on, with Apache and MySQL running in XAMPP.</li>
<li>Devices are on the same Wi-Fi (not mobile data, not a separate guest Wi-Fi).</li>
<li>Windows Firewall allows Apache on the Private network.</li></ol></div>

<div class="card"><h2>B. From any network (home, mobile data, other towns)</h2>
<p>Your computer must give the system a public <b>https</b> address. Pick one method, then paste the address it gives you into the box at the bottom of this page.</p>
<h3 style="font-size:15px;margin:10px 0 4px">Method 1: Tailscale Funnel (free, address stays the same)</h3><ol>
<li>Install Tailscale from tailscale.com/download on this computer and sign in.</li>
<li>Open PowerShell and run: <b>tailscale funnel --bg 80</b>. The first time, it shows a link to approve Funnel; open it and approve.</li>
<li>Run <b>tailscale funnel status</b>. It prints your address, like https://your-pc.your-network.ts.net</li>
<li>Your login link is that address + <b>/english-proficiency-management-system/login.php</b>. Users do not need to install anything.</li></ol>
<h3 style="font-size:15px;margin:10px 0 4px">Method 2: Cloudflare quick tunnel (free, fastest test, address changes each time)</h3><ol>
<li>Download <b>cloudflared</b> for Windows from the Cloudflare website.</li>
<li>In PowerShell, in the folder containing it, run: <b>cloudflared tunnel --url http://localhost:80</b></li>
<li>It prints an address ending in <b>trycloudflare.com</b>. Add <b>/english-proficiency-management-system/login.php</b>. Leave the window open; when it closes or the computer restarts, the address changes.</li></ol>
<p class="mu">For a permanent address with your own domain, use a named Cloudflare Tunnel. For a link that works even when this computer is off, move the system to web hosting that offers PHP and MySQL.</p></div>

<div class="card"><h2>Safety checklist before sharing publicly</h2><ul style="margin:0;padding-left:18px">
<li><?=$https?'✅ This page is using a secure (https) connection.':'⚠️ This page is not using https. Fine on the school Wi-Fi, but use Method 1 or 2 for access from other networks.'?></li>
<li><?=$inst?'⚠️ install.php is still on the server. Delete it now.':'✅ install.php has been removed.'?></li>
<li><?=$tmp?'⚠️ '.$tmp.' active account(s) still have a temporary password and must change it at first sign-in.':'✅ Every active user has set their own password.'?></li>
<li>✅ Sign-in is limited: 5 wrong passwords lock an account for 10 minutes, and 15 failed attempts block a device for 15 minutes.</li>
<li>Give each person only the role they need, and disable accounts of staff who leave (Accounts page).</li></ul>
<?php if($inst)echo '<form method="post" class="np" onsubmit="return confirm(\'Delete install.php?\')"><input type="hidden" name="t" value="'.csrf().'"><input type="hidden" name="a" value="rm"><div class="row"><button class="d">Delete install.php</button></div></form>';?></div>

<div class="card np"><h2>Save the address people should use</h2><form method="post"><input type="hidden" name="t" value="<?=csrf()?>">
<label>Full address including the folder name. Leave empty for automatic.</label>
<input name="u" value="<?=h($set)?>" placeholder="https://your-pc.your-network.ts.net/english-proficiency-management-system/"><br><br><button>Save</button></form></div><?php bot();
