<?php
// p2 -  ���C�ɔ̏���

require_once './filectl.class.php';

//===============================================
// �ϐ�
//===============================================

if (isset($_GET['setfavita'])) {
	$setfavita = $_GET['setfavita'];
} elseif (isset($_POST['setfavita'])) {
	$setfavita = $_POST['setfavita'];
}

$host = $_GET['host'];
$bbs = $_GET['bbs'];
if ($_POST['url']) {
	if (preg_match("/http:\/\/(.+)\/([^\/]+)\/([^\/]+\.html?)?/", $_POST['url'], $matches)) {
		$host = $matches[1];
		$bbs = $matches[2];
	} else {
		$_info_msg_ht .= "<p>p2 info: �u{$_POST['url']}�v�͔�URL�Ƃ��Ė����ł��B</p>";
	}
}

if (isset($_POST['itaj'])){
	$itaj = $_POST['itaj']; 
}
if (!isset($itaj) && isset($_GET['itaj_en'])) {
	$itaj = base64_decode($_GET['itaj_en']);
} 
if (empty($itaj)) { $itaj = $bbs; }

//================================================
// �ǂݍ���
//================================================
//favita_path�t�@�C�����Ȃ���ΐ���
FileCtl::make_datafile($_conf['favita_path'], $_conf['favita_perm']);

//favita_path�ǂݍ���;
$lines= @file($_conf['favita_path']);

//================================================
// ����
//================================================

//�ŏ��ɏd���v�f������
if($lines){
	$i=-1;
	unset($neolines);
	foreach($lines as $l){
		$i++;
		
		/* ���f�[�^�iver0.6.0�ȉ��j�ڍs�[�u */
		if(! preg_match("/^\t/", $l)){ $l="\t".$l;}
		/*------------------------*/
		
		$lar = explode("\t", $l);
		
		if( $lar[1]==$host and $lar[2]==$bbs ){ //�d�����
			$before_line_num=$i;
			continue;
		//}elseif(!$lar[3]){//�s���f�[�^�i���Ȃ��j���A�E�g
		//	continue;
		}else{
			$neolines[]=$l;
		}
	}
}

//�V�K�f�[�^�ݒ�
if($setfavita){
	if($host && $bbs && $itaj){
		$newdata="\t{$host}\t{$bbs}\t{$itaj}\n";
	}
}
	
if($setfavita==1 or $setfavita=="top"){
	$after_line_num=0;

}elseif($setfavita=="up"){
	$after_line_num=$before_line_num-1;
	if($after_line_num<0){$after_line_num=0;}
	
}elseif($setfavita=="down"){
	$after_line_num=$before_line_num+1;
	if( $after_line_num >= sizeof($neolines) ){$after_line_num="bottom";}
	
}elseif($setfavita=="bottom"){
	$after_line_num="bottom";
}

//================================================
//��������
//================================================
$fp = @fopen($_conf['favita_path'], "wb") or die("Error: {$_conf['favita_path']} ���X�V�ł��܂���ł���");
@flock($fp, LOCK_EX);
if ($neolines) {
	$i = 0;
	foreach ($neolines as $l) {
		if ($i === $after_line_num) { fputs($fp, $newdata); }
		fputs($fp, $l);
		$i++;
	}
	if ($after_line_num === "bottom") {	fputs($fp, $newdata); }
	//�u$after_line_num == "bottom"�v���ƌ듮�삷��B
} else {
	fputs($fp, $newdata);
}
@flock($fp, LOCK_UN);
fclose($fp);

?>