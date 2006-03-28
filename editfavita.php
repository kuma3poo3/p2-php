<?php
/*
    p2 -  お気に入り編集
*/

include_once './conf/conf.inc.php';  // 基本設定
include_once P2_LIBRARY_DIR . '/filectl.class.php';
include_once P2_LIBRARY_DIR . '/strctl.class.php';

$_login->authorize(); // ユーザ認証

//================================================================
// 特殊な前置処理
//================================================================

// お気に板の追加・削除、並び替え
if (isset($_GET['setfavita']) or isset($_POST['setfavita']) or isset($_POST['submit_setfavita'])) {
    include_once P2_LIBRARY_DIR . '/setfavita.inc.php';
    setFavIta();
}
// お気に板のホストを同期
if (isset($_GET['syncfavita']) or isset($_POST['syncfavita'])) {
    include_once P2_LIBRARY_DIR . '/BbsMap.class.php';
    BbsMap::syncBrd($_conf['favita_path']);
}


// プリント用変数 ======================================================

// お気に板追加フォーム
$add_favita_form_ht = <<<EOFORM
<form method="POST" action="{$_SERVER['PHP_SELF']}" accept-charset="{$_conf['accept_charset']}" target="_self">
    <input type="hidden" name="detect_hint" value="◎◇　◇◎">
    <p>
        {$_conf['k_input_ht']}
        板URL: <input type="text" id="url" name="url" value="http://" size="48">
        板名: <input type="text" id="itaj" name="itaj" value="" size="16">
        <input type="hidden" id="setfavita" name="setfavita" value="1">
        <input type="submit" name="submit" value="新規追加">
    </p>
</form>\n
EOFORM;

// お気に板同期フォーム
$sync_favita_form_ht = <<<EOFORM
<form method="POST" action="{$_SERVER['PHP_SELF']}" target="_self">
    <p>
        {$_conf['k_input_ht']}
        <input type="hidden" id="syncfavita" name="syncfavita" value="1">
        <input type="submit" name="submit" value="板リストとホストを同期する">（板のホスト移転に対応します）
    </p>
</form>\n
EOFORM;

// お気に板切替フォーム
if ($_conf['expack.misc.multi_favs']) {
    $switch_favita_form_ht = FavSetManager::makeFavSetSwitchForm('m_favita_set', 'お気に板', NULL, NULL, !$_conf['ktai']);
} else {
    $switch_favita_form_ht = '';
}

//================================================================
// ヘッダ
//================================================================
P2Util::header_nocache();
P2Util::header_content_type();
if ($_conf['doctype']) { echo $_conf['doctype']; }
echo <<<EOP
<html lang="ja">
<head>
    {$_conf['meta_charset_ht']}
    <meta name="ROBOTS" content="NOINDEX, NOFOLLOW">
    <meta http-equiv="Content-Style-Type" content="text/css">
    <meta http-equiv="Content-Script-Type" content="text/javascript">
    <title>p2 - お気に板の並び替え</title>\n
EOP;

if (empty($_conf['ktai'])) {
    echo <<<EOP
    <script type="text/javascript" src="js/yui/YAHOO.js?{$_conf['p2expack']}" ></script>
    <script type="text/javascript" src="js/yui/log.js?{$_conf['p2expack']}" ></script>
    <script type="text/javascript" src="js/yui/event.js?{$_conf['p2expack']}" ></script>
    <script type="text/javascript" src="js/yui/dom.js?{$_conf['p2expack']}"></script>
    <script type="text/javascript" src="js/yui/dragdrop.js?{$_conf['p2expack']}" ></script>
        <script type="text/javascript" src="js/yui/ygDDOnTop.js?{$_conf['p2expack']}" ></script>
        <script type="text/javascript" src="js/yui/ygDDSwap.js?{$_conf['p2expack']}" ></script>
        <script type="text/javascript" src="js/yui/ygDDMy.js?{$_conf['p2expack']}" ></script>
        <script type="text/javascript" src="js/yui/ygDDMy2.js?{$_conf['p2expack']}" ></script>
        <script type="text/javascript" src="js/yui/ygDDList.js?{$_conf['p2expack']}" ></script>
        <script type="text/javascript" src="js/yui/ygDDPlayer.js?{$_conf['p2expack']}" ></script>
    <link rel="stylesheet" href="css.php?css=style&amp;skin={$skin_en}" type="text/css">
    <link rel="stylesheet" href="css.php?css=editfavita&amp;skin={$skin_en}" type="text/css">\n
EOP;
}

$body_at = ($_conf['ktai']) ? $_conf['k_colors'] : ' onLoad="top.document.title=self.document.title;"';
echo "</head><body{$body_at}>\n";

echo $_info_msg_ht;
$_info_msg_ht = '';

//================================================================
// メイン部分HTML表示
//================================================================

//================================================================
// お気に板
//================================================================

// favitaファイルがなければ生成
FileCtl::make_datafile($_conf['favita_path'], $_conf['favita_perm']);
// favita読み込み
$lines = file($_conf['favita_path']);
$okini_itas = array();

$i = 0;
if (is_array($lines)) {
    foreach ($lines as $l) {
        if (preg_match("/^\t?(.+?)\t(.+?)\t(.+?)$/", rtrim($l), $matches)) {
            $id = "li{$i}";
            $okini_itas[$id]['itaj']       = $itaj = rtrim($matches[3]);
            $okini_itas[$id]['itaj_en']    = $itaj_en = base64_encode($itaj);
            $okini_itas[$id]['host']       = $host = $matches[1];
            $okini_itas[$id]['bbs']        = $bbs = $matches[2];
            $okini_itas[$id]['itaj_view']  = htmlspecialchars($itaj);
            $okini_itas[$id]['itaj_ht']    = "&amp;itaj_en=" . $itaj_en;
            $okini_itas[$id]['value']      = StrCtl::toJavaScript("{$host}@{$bbs}@{$itaj_en}");

            $i++;
        }
    }
}

// PC用
if (empty($_conf['ktai']) and !empty($lines)) {
?>
<script type="text/javascript">
    // var gLogger = new ygLogger("test_noimpl.php");
    var dd = []
    var gVarObj = new Object();

    function dragDropInit() {
        var i = 0;
        var id = '';
        for (j = 0; j < <?php echo count($lines); ?>; ++j) {
            id = "li" + j;
            dd[i++] = new ygDDList(id);
            //gVarObj[id] = '<?php echo $host . "@" . $bbs . "@" . $itaj_en; ?>';
        }
        <?php
        foreach ($okini_itas as $k => $v) {
            echo "gVarObj['{$k}'] = '{$v['value']}';";
        }
        ?>

        dd[i++] = new ygDDListBoundary("hidden1");

        YAHOO.util.DDM.mode = 0; // 0:Point, :Intersect
    }

    YAHOO.util.Event.addListener(window, "load", dragDropInit);
    // YAHOO.util.DDM.useCache = false;


function makeOptionList()
{
    var values = [];
    var elem = document.getElementById('italist');
    var childs = elem.childNodes;
    for (var i = 0; i < childs.length; i++) {
        if (childs[i].tagName == 'LI' && childs[i].style.visibility != 'hidden' && childs[i].style.display != 'none') {
            values[i] = gVarObj[childs[i].id];
            //alert(values[i]);
        }
    }

    var val = "";
    for (var j = 0; j < values.length; j++) {
        if (val > "") {
            val += ",";
        }
        if (values[j] > "") {
            val += values[j];
        }
    }
    //alert(val);

    return val;
}

function submitApply()
{
    document.form['list'].value = makeOptionList();
    //alert(document.form['list'].value);
    //document.form.submit();
}
</script>
<?php
}


// PC用
if (empty($_conf['ktai'])) {
    $onclick = " onClick='if (parent.menu) { parent.menu.location.href=\"{$_conf['menu_php']}?nr=1\"; }'";
    $m_php = $_SERVER['PHP_SELF'];

// 携帯用
} else {
    $onclick = '';
    $m_php = 'menu_k.php?view=favita&amp;nr=1' . $_conf['k_at_a'] . '&amp;nt=' . time();
}

echo <<<EOP
<div><b>お気に板の編集</b> [<a href="{$m_php}"{$onclick}>メニューを更新</a>] {$switch_favita_form_ht}</div>
EOP;

echo $add_favita_form_ht;
echo '<hr>';

// PC（NetFrontを除外）
if (empty($_conf['ktai']) && $_conf['favita_order_dnd'] && !P2Util::isNetFront()) {

    if ($lines) {
        $script_enable_html .= <<<EOP
お気に板の並び替え（ドラッグアンドドロップ）
<div class="itas">
<form id="form" name="form" method="post" action="{$_SERVER['PHP_SELF']}" accept-charset="{$_conf['accept_charset']}" target="_self">

<table border="0">
<tr>
<td class="italist" id="ddrange">

<ul id="italist"><li id="hidden6" class="sortList" style="visibility:hidden;">Hidden</li>
EOP;
        if (is_array($okini_itas)) {
            foreach ($okini_itas as $k => $v) {
                $script_enable_html .= '<li id="' . $k . '" class="sortList">' . $v['itaj_view'] . '</li>';
            }
        }
    }

    $script_enable_html .= <<<EOP
<li id="hidden1" style="visibility:hidden;">Hidden</li></ul>

</td>
</tr>
</table>

<input type="hidden" name="list">

<input type="submit" value="元に戻す">
<input type="submit" name="submit_setfavita" value="変更を適用する" onClick="submitApply();">

</div>
</form>
EOP;
    $regex = array('/"/', '/\n/');
    $replace = array('\"', null);
    $out = preg_replace($regex, $replace, $script_enable_html);

    echo <<<EOP
<script type="text/javascript">
<!--
document.write("{$out}");
//-->
</script>
EOP;

}

//================================================================
// NOSCRIPT時のHTML表示
//================================================================
if ($lines) {
    // PC（NetFrontを除外）
    if (empty($_conf['ktai']) && $_conf['favita_order_dnd'] && !P2Util::isNetFront()) {
        echo '<noscript>';
    }
    echo 'お気に板の並び替え';
    echo '<table>';
    foreach ($lines as $l) {
        if (preg_match('/^\t?(.+?)\t(.+?)\t(.+?)$/', rtrim($l), $matches)) {
            $itaj       = rtrim($matches[3]);
            $itaj_en    = rawurlencode(base64_encode($itaj));
            $host       = $matches[1];
            $bbs        = $matches[2];
            $itaj_view  = htmlspecialchars($itaj, ENT_QUOTES);
            $itaj_q     = '&amp;itaj_en='.$itaj_en;
            echo <<<EOP
            <tr>
            <td><a href="{$_conf['subject_php']}?host={$host}&amp;bbs={$bbs}{$_conf['k_at_a']}">{$itaj_view}</a></td>
            <td>[ <a class="te" href="{$_SERVER['PHP_SELF']}?host={$host}&amp;bbs={$bbs}{$itaj_q}&amp;setfavita=top{$_conf['k_at_a']}" title="一番上に移動">▲</a></td>
            <td><a class="te" href="{$_SERVER['PHP_SELF']}?host={$host}&amp;bbs={$bbs}{$itaj_q}&amp;setfavita=up{$_conf['k_at_a']}" title="一つ上に移動">↑</a></td>
            <td><a class="te" href="{$_SERVER['PHP_SELF']}?host={$host}&amp;bbs={$bbs}{$itaj_q}&amp;setfavita=down{$_conf['k_at_a']}" title="一つ下に移動">↓</a></td>
            <td><a class="te" href="{$_SERVER['PHP_SELF']}?host={$host}&amp;bbs={$bbs}{$itaj_q}&amp;setfavita=bottom{$_conf['k_at_a']}" title="一番下に移動">▼</a> ]</td>
            <td>[<a href="{$_SERVER['PHP_SELF']}?host={$host}&amp;bbs={$bbs}&amp;setfavita=0{$_conf['k_at_a']}">削除</a>]</td>
            </tr>
EOP;
        }
    }

    echo "</table>";
    // PC（NetFrontを除外）
    if (empty($_conf['ktai']) && $_conf['favita_order_dnd'] && !P2Util::isNetFront()) {
        echo '</noscript>';
    }
}

// PC
if (empty($_conf['ktai'])) {
    echo '<hr>';
    echo $sync_favita_form_ht;
}

//================================================================
// フッタHTML表示
//================================================================
if ($_conf['ktai']) {
    echo '<hr>' . $_conf['k_to_index_ht'];
}

echo '</body></html>';

?>
