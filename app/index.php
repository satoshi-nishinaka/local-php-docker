<?php

header('Content-Type: text/html; charset=UTF-8');

mb_internal_encoding('UTF-8');
mb_regex_encoding('UTF-8');

error_reporting(E_ALL);

require_once 'EvalExecutor.php';

$debugger = new EvalExecutor();

?>
<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Transitional//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd'>
<html xmlns='http://www.w3.org/1999/xhtml'>
<head>
    <title>デバッガ</title>
    <meta http-equiv='X-UA-Compatible' content='IE=edge'>
    <meta name='viewport' content='width=device-width, initial-scale=1'>
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <META HTTP-EQUIV='Cache-Control' content='no-cache'>
    <META HTTP-EQUIV='Pragma' CONTENT='no-cache'>
    <meta http-equiv='Content-Type' content='text/html; charset=utf-8' />
    <!-- Bootstrap -->
    <link href='css/bootstrap.min.css' rel='stylesheet'>

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src='https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js'></script>
    <script src='https://oss.maxcdn.com/respond/1.4.2/respond.min.js'></script>
    <![endif]-->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js" integrity="sha256-2Pmvv0kuTBOenSvLm6bvfBSSHrUJ+3A7x6P5Ebd07/g=" crossorigin="anonymous"></script>
    <script type='text/javascript' src='js/tabIndent.js'></script>
    <script type='text/javascript'>
        $(document).ready(function() {
            $('input[name="reset"]').on('click', () => {
                console.log('Clear All');
                $('input[name="mode"]').val('eval')
                $('textarea[name="exescript"]').val('');
                $('input[name="counter"]').val('1');
                $('input[name="limittime"]').val('30');
                $('input[name="filename"]').val('');
                console.log('Clear All');
            });
            tabIndent.renderAll();
        });
    </script>
    <style type='text/css'>
        body { margin: 1em; }
        table.tbl {
            border-collapse: collapse;
            padding: 2px;
            border: 1px solid #000;
            font-weight: normal;
            font-size: 10pt;
            color: #000;
        }
        .tbl th, .tbl td {
            text-align: left;
            padding: 2px;
            border: 1px solid #000;
        }
        .tbl tr:nth-child(even) {
            background-color: #EEE;
        }
        .tbl tr:nth-child(odd) {
            background-color: #CCC;
        }
        textarea.form-control {
            width: 90%;
            height: 40%;
        }
        .btn {
            margin: 2px 0;
        }
        .form-control {
            width: initial;
            display: inline-block;
        }
        input[type='text'].form-control {
            margin: 2px;
        }
        .php_official {
            text-align: right;
            font-weight: bold;
        }
    </style></head>
<body>
<div class='btn btn-primary' style='position: fixed;top: 1em; right: 1em; padding: 4px; z-index: 100;'>
    <a href='#top' style='text-decoration: none; color: #fff; font-weight: bold; background-color: transparent;'>TOP</a>
</div>
<div class='btn btn-primary' style='position: fixed;bottom: 1em; right: 1em; padding: 4px; z-index: 100;'>
    <a href='#bottom' style='text-decoration: none; color: #fff; font-weight: bold; background-color: transparent;'>BOTTOM</a>
</div>
<h1 id='top'><span style='background-color:white;'>PHP実行スクリプト</span>　≪ <?= $_SERVER['REQUEST_METHOD'] ?> ≫</h1>
<p class='php_official'><a href='https://www.php.net/' target='_blank'>[PHP: Hypertext Preprocessor]</a></p>
<form name='_form' method='post' action='<?= $_SERVER['PHP_SELF'] ?>'>
    <div>
        <input type='hidden' name='mode' value="eval" />
        <textarea rows='20' cols='' class='form-control tabIndent' name='exescript'><?= $debugger->receiveString ?></textarea><br />
        <input type='button' class='btn btn-primary' name='exe' value='実行' onclick='_form.submit();' />
        <input type='button' class='btn btn-info' name='reset' value='クリア' />
        実行回数<input type='text' class='form-control' name='counter' value='<?= $debugger->repeatCounter ?>' size='10' />回
        最大待機時間<input type='text' class='form-control' name='limittime' value='<?= $debugger->limitTime ?>' size='6' maxlength='3' />秒　
        <label><input type='checkbox' name='pre' value='preon' <?= ($debugger->usePreTag ? 'checked' : '') ?>/>&lt;pre&gt;をつける</label>
    </div>
    <div>
        <input type='button' class='btn btn-success' name='strlen' value='strlen' onclick='_form.mode.value="strlen";_form.submit();' />
        <input type='button' class='btn btn-success' name='bin2hex' value='bin2hex' onclick='_form.mode.value="bin2hex";_form.submit();' />
        <input type='button' class='btn btn-success' name='base_convert10-&gt;16' value='base_convert10-&gt;16' onclick='_form.mode.value="baseconvert16";_form.submit();' />
        <input type='button' class='btn btn-success' name='base_convert16-&gt;10' value='base_convert16-&gt;10' onclick='_form.mode.value="baseconvert10";_form.submit();' />
        <input type='button' class='btn btn-success' name='phpinfo' value='PHPINFO' onclick='_form.mode.value="phpinfo";_form.submit();' />
        <input type='button' class='btn btn-success' name='server' value='server' onclick='_form.mode.value="server";_form.submit();' />
    </div>
    <div class='radio'>
        <label><input type='radio' name='convert' value='eval' <?= ($debugger->convert === 'eval' ? 'checked' : "") ?> />スクリプトの実行</label>　
        <label><input type='radio' name='convert' value='strtolowwer' <?=  ($debugger->convert == 'strtolowwer' ? 'checked' : "") ?> />テキストを小文字化する</label>　
        <label><input type='radio' name='convert' value='strtoupper' <?= ($debugger->convert == 'strtoupper' ? 'checked' : "") ?> />テキストを大文字化する</label>　
        <label><input type='radio' name='convert' value='mb_convert_hankaku' <?= ($debugger->convert == 'mb_convert_hankaku' ? 'checked' : "") ?> />テキストを半角化する</label>　
        <label><input type='radio' name='convert' value='print' <?= ($debugger->convert == 'print' ? 'checked' : "") ?> />テキストをPHPのPRINT文に変換する</label><br />
        インデントスペース数<input type='text' class='form-control' name='indentsize' value='<?= $debugger->indentSize ?>' size='10' maxlength='2' />
        <input type='button' class='btn btn-success' name='indent' value='インデント付与' onClick='_form.mode.value="indent";_form.submit();' />
    </div>
    <hr />
    <div class='alert alert-dismissible alert-info'>
        <?php
        $debugger->execute()->output();
        ?>
        <br id='bottom' />
    </div>
</form>
</body>
</html>
