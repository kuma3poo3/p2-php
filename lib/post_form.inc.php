<?php
/**
 *  p2 書き込みフォーム
 */

if (!empty($_conf['ktai'])) {
    $htm['k_br'] = '<br>';
    $htm['on_js'] = '';
} else {
    $htm['k_br'] = '';
    $htm['kaiko_on_js'] = ' onFocus="adjustTextareaRows(this, ' . $STYLE['post_msg_rows'] . ', 2);" onKeyup="adjustTextareaRows(this, ' . $STYLE['post_msg_rows'] . ', 2);"';
}

// 文字コード判定用文字列を先頭に仕込むことでmb_convert_variables()の自動判定を助ける
$htm['post_form'] = <<<EOP
{$htm['disable_js']}
{$htm['resform_ttitle']}
{$htm['orig_msg']}
<form id="resform" method="POST" action="./post.php" accept-charset="{$_conf['accept_charset']}" onsubmit="disableSubmit(this)">
    <input type="hidden" name="detect_hint" value="◎◇">
    {$htm['subject']}
    {$htm['maru_post']} 名前： <input id="FROM" name="FROM" type="text" value="{$hd['FROM']}"{$name_size_at}>{$htm['k_br']} 
     E-mail : <input id="mail" name="mail" type="text" value="{$hd['mail']}"{$mail_size_at}{$on_check_sage}>
    {$sage_cb_ht}
    <textarea id="MESSAGE" name="MESSAGE" rows="{$STYLE['post_msg_rows']}"{$msg_cols_at} wrap="{$wrap}"{$htm['kaiko_on_js']}>{$hd['MESSAGE']}</textarea>{$htm['k_br']}
    <input type="submit" name="submit" value="{$submit_value}" onClick="setHiddenValue(this);">
    {$htm['be2ch']}
    <br>
    {$htm['src_fix']}
    
    <input type="hidden" name="bbs" value="{$bbs}">
    <input type="hidden" name="key" value="{$key}">
    <input type="hidden" name="time" value="{$time}">
    
    <input type="hidden" name="host" value="{$host}">
    <input type="hidden" name="popup" value="{$popup}">
    <input type="hidden" name="rescount" value="{$rescount}">
    <input type="hidden" name="ttitle_en" value="{$ttitle_en}">
    <input type="hidden" name="csrfid" value="{$csrfid}">
    {$newthread_hidden_ht}{$readnew_hidden_ht}
    {$_conf['k_input_ht']}
</form>\n
EOP;



?>
