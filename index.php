<?php
$cfg = array(
    'title' => 'MEYHANE BATTLEPASS',
    'season' => 'SEZON 1',
    'level_xp' => 500,
    'weekly_bonus' => 250,
    'quests' => array(
        array('50 Headshot', 50, 50, 100, '24 saat icinde 50 headshot yap.'),
        array('300 Kill', 300, 75, 150, '24 saat icinde toplam 300 rakip oldur.'),
        array('25 Bicak Killi', 25, 50, 125, '24 saat icinde 25 bicak killi yap.'),
        array('120 Aktif Dakika', 120, 25, 75, 'AFK olmadan 120 dakika aktif kal.')
    ),
    'streaks' => array(array(3,25),array(7,75),array(14,150))
);
function gi($k,$d=0){ return isset($_GET[$k]) ? intval($_GET[$k]) : $d; }
function gs($k,$d=''){ return isset($_GET[$k]) ? trim((string)$_GET[$k]) : $d; }
function e($s){ return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
function clampv($v,$min,$max){ if($v<$min)return $min; if($v>$max)return $max; return $v; }
function percent($v,$max){ return $max>0 ? clampv(intval(($v*100)/$max),0,100) : 0; }
function bit_on($mask,$i){ return (($mask & (1 << $i)) != 0); }
function q_status($i,$progress,$quest,$claimedMask){ if(bit_on($claimedMask,$i)) return 'ALINDI'; if($progress >= $quest[1]) return 'ODUL HAZIR'; return 'DEVAM EDIYOR'; }
function q_class($status){ if($status==='ODUL HAZIR') return 'status-ready'; if($status==='ALINDI') return 'status-done'; return 'status-progress'; }
function nav_url($page,$base){ $base['page']=$page; return '?'.http_build_query($base,'','&'); }

$name = str_replace('_',' ', gs('name','OYUNCU'));
$lv = max(1, gi('lv',1));
$xp = max(0, gi('xp',0));
$xm = max(1, gi('xm',$cfg['level_xp']));
$coin = max(0, gi('coin',0));
$st = max(0, gi('st',0));
$day = clampv(gi('day',0),0,6);
$wm = max(0, gi('wm',0));
$wb = gi('wb',0) ? 1 : 0;
$wr = gs('wr','-');
$cm = max(0, gi('cm',0));
$p = array(
  clampv(gi('p0',0),0,$cfg['quests'][0][1]),
  clampv(gi('p1',0),0,$cfg['quests'][1][1]),
  clampv(gi('p2',0),0,$cfg['quests'][2][1]),
  clampv(gi('p3',0),0,$cfg['quests'][3][1])
);
$page = gs('page','gorevler');
$allowed = array('gorevler','oduller','sezon','profil','nasil');
if(!in_array($page,$allowed,true)) $page='gorevler';
$days = array('PZT','SAL','CRS','PRS','CUM','CMT','PAZ');
$xpPct = percent($xp,$xm);
$weekDone = 0; for($i=0;$i<7;$i++) if(bit_on($wm,$i)) $weekDone++;
$base = array('name'=>gs('name','OYUNCU'),'lv'=>$lv,'xp'=>$xp,'xm'=>$xm,'coin'=>$coin,'st'=>$st,'day'=>$day,'wm'=>$wm,'wb'=>$wb,'wr'=>$wr,'cm'=>$cm,'p0'=>$p[0],'p1'=>$p[1],'p2'=>$p[2],'p3'=>$p[3]);
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title><?php echo e($cfg['title']); ?></title>
<style type="text/css">
html,body{margin:0;padding:0;width:100%;height:100%;overflow:hidden;background:#02070d;color:#d9ecff;font-family:Tahoma,Arial,sans-serif;font-size:12px;}
body{position:relative;}
a{text-decoration:none;}
#bp-shell{position:absolute;left:0;top:0;width:960px;height:680px;overflow:hidden;transform-origin:0 0;-webkit-transform-origin:0 0;}
.outer{width:960px;height:680px;background:#04101a;border:1px solid #8d6223;box-sizing:border-box;table-layout:fixed;}
.hero{height:116px;position:relative;overflow:hidden;border-bottom:1px solid #8d6223;background:#07141f;}
.hero img{width:100%;height:100%;display:block;border:0;}
.hero-title{position:absolute;left:0;top:48%;width:100%;margin-top:-14px;text-align:center;font-family:Georgia,serif;font-size:28px;font-weight:bold;color:#efc46a;text-shadow:2px 2px #000;letter-spacing:2px;}
.hero-season{position:absolute;left:0;top:76%;width:100%;text-align:center;font-size:13px;font-weight:bold;color:#53c8ff;letter-spacing:3px;text-shadow:1px 1px #000;}
.profile{height:74px;background:#081725;border-bottom:1px solid #8d6223;}
.profile td{padding:6px 8px;border-right:1px solid #18354b;vertical-align:middle;}
.label{display:block;color:#88a7bc;font-size:10px;margin-bottom:1px;}.player-name{font-size:17px;color:#f3c561;font-weight:bold;}.stat-gold{font-size:15px;color:#f3c561;font-weight:bold;}.stat-blue{font-size:15px;color:#38c6ff;font-weight:bold;}
.bar{width:100%;height:10px;background:#060f17;border:1px solid #35566b;}.bar td{height:10px;padding:0;border:0;}.bar-fill{background:#2abcf4;}.bar-green{background:#4add6a;}
.week{height:54px;background:#030b12;border-bottom:1px solid #8d6223;}.week td{padding:2px;}
.day{height:30px;background:#07141d;color:#718ba2;text-align:center;border:1px solid #173248;font-size:10px;}.day b{display:block;font-size:11px;color:#b7d1e3;margin-top:1px;}.day.today{border-color:#2ea4ea;background:#0a3960;color:#8de2ff;}.day.today b{color:#b7efff;}.day.done{border-color:#2f9348;background:#0d2b14;color:#8effa5;}.day.done b{color:#cbffd2;}.day.missed{background:#1a0d0a;color:#92665a;}
.main{height:404px;}.sidebar{width:210px;background:#030a10;border-right:1px solid #8d6223;padding:7px;vertical-align:top;}.content{background:#06111a;padding:8px;vertical-align:top;overflow:hidden;}
.nav{display:block;margin-bottom:6px;padding:10px 10px 10px 12px;background:#2c1b08;border:1px solid #6c491c;color:#edca81;font-weight:bold;font-size:12px;}.nav span{display:block;color:#876f4a;font-size:9px;font-weight:normal;margin-top:2px;letter-spacing:1px;}.nav.active{background:#0a3b5f;border-color:#2f9fdd;color:#80ddff;}.nav.active span{color:#a5dfff;}
.sidebar-box{margin-top:9px;border:1px solid #24475d;background:#081420;padding:8px;text-align:center;}.box-title{color:#7c99ae;font-size:10px;}.box-value{color:#f2c462;font-size:14px;font-weight:bold;}.box-sub{display:block;color:#53c8ff;font-size:11px;margin-top:3px;}
.section-title{background:#112a3d;border-left:4px solid #2ea8ea;border-bottom:1px solid #31586e;color:#f0c566;font-size:17px;font-weight:bold;padding:8px 10px;margin-bottom:6px;}
.quests{width:100%;border:1px solid #1a364a;}.qhead td{background:#30200d;color:#efc25f;font-weight:bold;padding:6px;border-right:1px solid #654315;font-size:11px;}.qrow td{padding:6px;border-bottom:1px solid #173549;border-right:1px solid #173549;background:#07131d;}.qrow.alt td{background:#091825;}.lvl{width:10%;color:#f1c96c;font-weight:bold;font-size:11px;}.qname{width:35%;color:#fff;font-size:12px;}.qname span{display:block;color:#8ba2b2;font-size:9px;margin-top:2px;line-height:1.2;}.qprogress{width:22%;color:#fff;font-size:11px;}.reward{width:16%;color:#f1c96c;font-weight:bold;font-size:11px;}.reward span{color:#36c4ff;display:block;margin-top:2px;}.statuswrap{width:17%;}.status{padding:6px 4px;text-align:center;font-weight:bold;border:1px solid #148cd0;font-size:10px;}.status-progress{background:#06365a;color:#54caff;}.status-ready{background:#16491c;border-color:#39bb51;color:#9dffab;}.status-done{background:#1e252d;border-color:#566473;color:#b8c4ce;}
.smallbar{width:100%;margin-top:4px;}.tip{margin-top:6px;padding:7px;border:1px solid #2b4558;background:#07141e;color:#b2c7d8;font-size:11px;}.tip b{color:#efc15f;}
.reward-list,.profile-table,.howto{width:100%;border:1px solid #1a364a;}.reward-list td,.profile-table td,.howto td{padding:9px;border-bottom:1px solid #183649;background:#07131d;font-size:12px;}.reward-list tr.alt td,.profile-table tr.alt td,.howto tr.alt td{background:#091825;}
.reward-name{color:#fff;font-weight:bold;}.reward-coin{color:#efc15f;font-weight:bold;}.reward-xp{color:#37c5ff;font-weight:bold;}
.big-card{margin-top:7px;text-align:center;padding:12px;border:1px solid #835d1f;background:#171006;color:#d8eaf7;}.card-title{font-size:13px;font-weight:bold;color:#efc15f;}.big-num{font-size:24px;font-weight:bold;color:#42caff;}
.season-grid{width:100%;}.season-card{width:25%;padding:10px 6px;text-align:center;border:1px solid #4e3b1d;background:#0b151d;}.story{margin-top:7px;padding:11px;border:1px solid #25455c;background:#071521;color:#c1d5e4;line-height:1.4;font-size:12px;}.story b{color:#efc15f;}
.profile-table td:first-child{width:40%;color:#8aa9bd;}.profile-table td:last-child{color:#f3c363;font-weight:bold;}.step{width:42px;text-align:center;color:#3bc5ff;font-size:17px;font-weight:bold;}
.footer{height:32px;background:#02090f;border-top:1px solid #8d6223;color:#7692a7;}.footer td{padding:6px 8px;font-size:11px;}.footer b{color:#d6a94c;}
</style>
</head>
<body>
<div id="bp-shell">
<table class="outer" cellspacing="0" cellpadding="0">
<tr><td class="hero">
  <img src="header.jpg" alt="Meyhane">
  <div class="hero-title"><?php echo e($cfg['title']); ?></div>
  <div class="hero-season"><?php echo e($cfg['season']); ?></div>
</td></tr>
<tr><td class="profile">
  <table width="100%" height="100%" cellspacing="0" cellpadding="0"><tr>
    <td width="31%"><span class="label">HOS GELDIN</span><span class="player-name"><?php echo e($name); ?></span></td>
    <td width="12%"><span class="label">COIN</span><span class="stat-gold"><?php echo number_format($coin,0,',','.'); ?></span></td>
    <td width="12%"><span class="label">LEVEL</span><span class="stat-blue"><?php echo $lv; ?></span></td>
    <td width="33%"><span class="label">BATTLEPASS XP &nbsp; <?php echo $xp; ?> / <?php echo $xm; ?></span><table class="bar" cellspacing="0" cellpadding="0"><tr><td class="bar-fill" width="<?php echo $xpPct; ?>%">&nbsp;</td><td width="<?php echo 100-$xpPct; ?>%">&nbsp;</td></tr></table></td>
    <td width="12%"><span class="label">STREAK</span><span class="stat-gold"><?php echo $st; ?> GUN</span></td>
  </tr></table>
</td></tr>
<tr><td class="week">
  <table width="100%" height="100%" cellspacing="3" cellpadding="0"><tr>
    <?php for($i=0;$i<7;$i++): $cls='day'; if(bit_on($wm,$i)) $cls.=' done'; elseif($i===$day) $cls.=' today'; elseif($i<$day) $cls.=' missed'; ?>
      <td width="14%" class="<?php echo $cls; ?>"><b><?php echo $days[$i]; ?></b><?php if(bit_on($wm,$i)): ?>TAMAM<?php elseif($i===$day): ?>BUGUN<?php elseif($i<$day): ?>KACIRILDI<?php else: ?>KILITLI<?php endif; ?></td>
    <?php endfor; ?>
  </tr></table>
</td></tr>
<tr><td class="main">
  <table width="100%" height="100%" cellspacing="0" cellpadding="0"><tr>
    <td class="sidebar" valign="top">
      <?php $nav = array('gorevler'=>array('GOREVLER','GUNLUK LISTE'),'oduller'=>array('ODULLER','COIN + XP'),'sezon'=>array('SEZON','STREAK & BONUS'),'profil'=>array('PROFIL','ISTATISTIK'),'nasil'=>array('NASIL OYNANIR?','KULLANIM')); foreach($nav as $k=>$info): ?>
        <a class="nav <?php echo ($page===$k?'active':''); ?>" href="<?php echo e(nav_url($k,$base)); ?>"><?php echo e($info[0]); ?><span><?php echo e($info[1]); ?></span></a>
      <?php endforeach; ?>
      <div class="sidebar-box"><span class="box-title">HAFTALIK BONUS</span><br><span class="box-value"><?php echo $weekDone; ?>/7</span><span class="box-sub"><?php echo $cfg['weekly_bonus']; ?> COIN</span></div>
      <div class="sidebar-box"><span class="box-title">GUNLUK RESET</span><br><span class="box-value"><?php echo e($wr); ?></span></div>
    </td>
    <td class="content" valign="top">
      <?php if($page==='gorevler'): ?>
        <div class="section-title">GUNLUK GOREVLER</div>
        <table class="quests" cellspacing="0" cellpadding="0">
          <tr class="qhead"><td>LVL</td><td>GOREV</td><td>ILERLEME</td><td>ODUL</td><td>DURUM</td></tr>
          <?php for($i=0;$i<4;$i++): $q=$cfg['quests'][$i]; $pc=percent($p[$i],$q[1]); $status=q_status($i,$p[$i],$q,$cm); ?>
          <tr class="qrow <?php echo ($i%2?'alt':''); ?>">
            <td class="lvl">LVL <?php echo $i+1; ?></td>
            <td class="qname"><b><?php echo e($q[0]); ?></b><span><?php echo e($q[4]); ?></span></td>
            <td class="qprogress"><b><?php echo $p[$i]; ?> / <?php echo $q[1]; ?></b><table class="bar smallbar" cellspacing="0" cellpadding="0"><tr><td class="<?php echo ($pc>=100?'bar-green':'bar-fill'); ?>" width="<?php echo $pc; ?>%">&nbsp;</td><td width="<?php echo 100-$pc; ?>%">&nbsp;</td></tr></table></td>
            <td class="reward"><?php echo $q[2]; ?> COIN<span>+<?php echo $q[3]; ?> XP</span></td>
            <td class="statuswrap"><div class="status <?php echo q_class($status); ?>"><?php echo e($status); ?></div></td>
          </tr>
          <?php endfor; ?>
        </table>
        <div class="tip">Gorev tamamlaninca odulu almak icin oyun sohbetinde <b>/bpmenu</b> yaz.</div>
      <?php elseif($page==='oduller'): ?>
        <div class="section-title">ODUL MERKEZI</div>
        <table class="reward-list" cellspacing="0" cellpadding="0">
          <?php for($i=0;$i<4;$i++): $q=$cfg['quests'][$i]; $status=q_status($i,$p[$i],$q,$cm); ?>
          <tr class="<?php echo ($i%2?'alt':''); ?>"><td class="reward-name"><?php echo e($q[0]); ?></td><td class="reward-coin"><?php echo $q[2]; ?> COIN</td><td class="reward-xp">+<?php echo $q[3]; ?> XP</td><td><div class="status <?php echo q_class($status); ?>"><?php echo e($status); ?></div></td></tr>
          <?php endfor; ?>
        </table>
        <div class="big-card"><span class="card-title">HAFTALIK 7/7 BONUS</span><br><span class="big-num"><?php echo $weekDone; ?>/7</span><br><?php echo $cfg['weekly_bonus']; ?> Coin <?php echo ($wb?' - ALINDI':''); ?></div>
      <?php elseif($page==='sezon'): ?>
        <div class="section-title">SEZON 1 ODULLERI</div>
        <table class="season-grid" cellspacing="6" cellpadding="0"><tr>
          <?php foreach($cfg['streaks'] as $s): ?><td class="season-card"><span class="card-title"><?php echo $s[0]; ?> GUN STREAK</span><br><span class="big-num"><?php echo min($st,$s[0]); ?>/<?php echo $s[0]; ?></span><br><span class="reward-coin"><?php echo $s[1]; ?> COIN</span></td><?php endforeach; ?>
          <td class="season-card"><span class="card-title">HAFTALIK BONUS</span><br><span class="big-num"><?php echo $weekDone; ?>/7</span><br><span class="reward-coin"><?php echo $cfg['weekly_bonus']; ?> COIN</span></td>
        </tr></table>
        <div class="story"><b>MEYHANE SEZON KURALI</b><br>Her gun gir, gorevlerini tamamla ve serini bozma. Gunluk gorevler 24 saatte sifirlanir. Haftayi 7/7 bitiren oyuncu haftalik bonusu acar.</div>
      <?php elseif($page==='profil'): ?>
        <div class="section-title">OYUNCU PROFILI</div>
        <table class="profile-table" cellspacing="0" cellpadding="0">
          <tr><td>Oyuncu</td><td><?php echo e($name); ?></td></tr>
          <tr class="alt"><td>Coin</td><td><?php echo number_format($coin,0,',','.'); ?></td></tr>
          <tr><td>BattlePass Level</td><td><?php echo $lv; ?></td></tr>
          <tr class="alt"><td>BattlePass XP</td><td><?php echo $xp; ?> / <?php echo $xm; ?></td></tr>
          <tr><td>Streak</td><td><?php echo $st; ?> Gun</td></tr>
          <tr class="alt"><td>Haftalik Tamamlama</td><td><?php echo $weekDone; ?>/7</td></tr>
        </table>
      <?php else: ?>
        <div class="section-title">NASIL OYNANIR?</div>
        <table class="howto" cellspacing="0" cellpadding="0">
          <tr><td class="step">01</td><td>/kayit ile BattlePass hesabini olustur.</td></tr>
          <tr class="alt"><td class="step">02</td><td>/giris ile hesabina gir ve /bp ile BattlePass ekranini ac.</td></tr>
          <tr><td class="step">03</td><td>50 Headshot, 300 Kill, 25 Bicak Killi ve 120 Aktif Dakika gorevlerini tamamla.</td></tr>
          <tr class="alt"><td class="step">04</td><td>Hazir odulleri /bpmenu ile Coin + XP olarak al.</td></tr>
          <tr><td class="step">05</td><td>Streak ve haftalik bonus icin her gun gorev yap.</td></tr>
        </table>
      <?php endif; ?>
    </td>
  </tr></table>
</td></tr>
<tr><td class="footer"><table width="100%" height="100%" cellspacing="0" cellpadding="0"><tr><td><b>MEYHANE BATTLEPASS</b> &nbsp; /bp &nbsp; | &nbsp; Odul Merkezi: /bpmenu</td><td align="right">Sezon 1</td></tr></table></td></tr>
</table>
</div>
<script type="text/javascript">
(function(){
  var shell=document.getElementById("bp-shell");
  if(!shell) return;
  var DESIGN_W=960, DESIGN_H=680;
  function viewport(){
    var w=0,h=0;
    if(window.innerWidth){w=window.innerWidth;h=window.innerHeight;}
    if((!w||!h) && document.documentElement){w=document.documentElement.clientWidth;h=document.documentElement.clientHeight;}
    if((!w||!h) && document.body){w=document.body.clientWidth;h=document.body.clientHeight;}
    if(!w)w=800;if(!h)h=600;
    return {w:w,h:h};
  }
  function fit(){
    var v=viewport();
    var margin=4;
    var sx=(v.w-margin*2)/DESIGN_W;
    var sy=(v.h-margin*2)/DESIGN_H;
    var s=(sx<sy?sx:sy);
    if(s<0.45)s=0.45;
    if(s>1.40)s=1.40;
    var sw=DESIGN_W*s, sh=DESIGN_H*s;
    var x=Math.floor((v.w-sw)/2), y=Math.floor((v.h-sh)/2);
    if(x<0)x=0;if(y<0)y=0;
    shell.style.left=x+"px";shell.style.top=y+"px";
    /* Eski IE tabanli MOTDlerde zoom daha net calisir. */
    if(typeof shell.style.zoom !== "undefined") {
      shell.style.zoom=s;
      shell.style.webkitTransform="";
      shell.style.transform="";
      shell.style.left=Math.floor((v.w-DESIGN_W*s)/2)+"px";
      shell.style.top=Math.floor((v.h-DESIGN_H*s)/2)+"px";
    } else {
      var t="scale("+s+")";
      shell.style.webkitTransform=t;
      shell.style.transform=t;
    }
  }
  fit();
  setTimeout(fit,80);
  setTimeout(fit,250);
  setTimeout(fit,700);
  if(window.attachEvent) window.attachEvent("onresize",fit);
  else if(window.addEventListener) window.addEventListener("resize",fit,false);
})();
</script>
</body>
</html>
