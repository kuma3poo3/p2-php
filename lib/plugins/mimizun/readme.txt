rep2+Wiki みみずんID検索ポップアッププラグイン

インストール方法
1.ファイルの設置
read.phpと同じディレクトリに以下のようにディレクトリ構造を作る。
read.php (rep2に含まれています)
img/spacer.gif (rep2に含まれています)
mimizun.php
lib/plugins/mimizun/mimizun.png
lib/plugins/mimizun/Mimizun.php
2.置換ワード
置換ワードの日付に以下の項目を追加。
Match=$
Replace=<a href="mimizun.php?bbs=$bbs&amp;id=$id" onmouseover="showHtmlPopUp('mimizun.php?bbs=$bbs&amp;id=$id',event,0.2)" onmouseout="offHtmlPopUp()" title="このIDをみみずんID検索で表示" target="_blank"><img src="mimizun.php?img=1&amp;host=$host&amp;bbs=$bbs" height=12px></a>

みみずんID検索に対応してる板ではみみずんID検索のボタン[mimizun]とみみずんID検索のポップアップリンクが表示されます。
対応していない板では横幅1px*12pxの画像が表示されます。
