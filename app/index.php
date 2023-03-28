<?php

header("Content-Type: text/html; charset=UTF-8");

mb_internal_encoding("UTF-8");
mb_regex_encoding("UTF-8");
include_once "common.php";

error_reporting(E_ALL);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
   <title>デバッガ</title>
  <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
  <META HTTP-EQUIV="Cache-Control" content="no-cache">
  <META HTTP-EQUIV="Pragma" CONTENT="no-cache">
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <!-- Bootstrap -->
    <link href="css/bootstrap.min.css" rel="stylesheet">

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
  <script type="text/javascript" src='js/jquery-2.1.1.min.js'></script>
  <script type="text/javascript" src="js/tabIndent.js"></script>
  <script type="text/javascript">
    $(document).ready(function() {
      $("input[name='reset']").click(function() {
        console.log("Clear All");
        $("textarea[name='exescript']").val("");
        $("input[name='counter']").val("1");
        $("input[name='limittime']").val("30");
        $("input[name='filename']").val("");
        console.log("Clear All");
      });
      tabIndent.renderAll();
    });
  </script>
  <style type="text/css">
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
    input[type="text"].form-control {
      margin: 2px;
    }
    .php_official {
      text-align: right;
      font-weight: bold;
    }
  </style></head>
<body>
<div class="btn btn-primary" style="position: fixed;top: 1em; right: 1em; padding: 4px; z-index: 100;">
  <a href="#top" style="text-decoration: none; color: #fff; font-weight: bold; background-color: transparent;">TOP</a>
</div>
<div class="btn btn-primary" style="position: fixed;bottom: 1em; right: 1em; padding: 4px; z-index: 100;">
  <a href="#bottom" style="text-decoration: none; color: #fff; font-weight: bold; background-color: transparent;">BOTTOM</a>
</div>
<h1 id="top"><span style="background-color:white;">PHP実行スクリプト</span>　≪ <?php echo $_SERVER['REQUEST_METHOD'] ?> ≫</h1>
<p class="php_official"><a href="https://www.php.net/" target="_blank">[PHP: Hypertext Preprocessor]</a></p>
<p><a href="./" class="btn btn-default btn-warning">back</a></p>

<?php

class PhpDebug {
  public $repeatCounter = 1;
  public $limitTime = 30;
  public $displayPreTag = false;
  public $convert = null;
  public $receive_strings = '';
  public $indentSize = 0;

  public function __construct() {
    if($_SERVER['REQUEST_METHOD'] != "POST") {
      return;
    }

    $this->initialize();
  }

  public function execute() {
    $this->_execute(filter_input(INPUT_POST, 'mode'));
  }

  private function initialize() {
    // 処理繰り返し回数
    $counter = filter_input(INPUT_POST, 'counter');
    if(!is_null($counter)) {
      $this->repeatCounter = (integer) $counter < 1 ? 1 : $counter;
    }
    // 実行時間制限
    $limitTime = filter_input(INPUT_POST, 'limittime');
    if(!is_null($limitTime)) {
      $this->limitTime = (integer) $limitTime < 600 ? 30 : $limitTime;
    }
    // preタグで囲んで実行結果を表示するか？
    $pre = filter_input(INPUT_POST, 'pre');
    if(!is_null($pre)) {
      $this->displayPreTag = true;
    }
    $convert = filter_input(INPUT_POST, 'convert');
    if(!is_null($convert) && 0 < strlen($convert)) {
      $this->convert = $convert;
    }
    set_time_limit($this->limitTime);

    // POSTされた文字列を格納
    $this->receive_strings = filter_input(INPUT_POST, 'exescript');

    $indentSize = filter_input(INPUT_POST, 'indentsize');
    if(is_numeric($indentSize)) {
      $this->indentSize = $indentSize;
    }
  }
  private function _execute_eval() {

    $script = trim($this->receive_strings);

    $script = preg_replace("/(^<\?(php)?)|(\?>$)/", "", $script);

    if(strlen($script) != 0 && !endwith($script, ';')) {
      $script .= ";";
    }

    if(strlen($script) != 0) {
      $StartTime = microtime(true);
      for($Cnt = 0; $Cnt < (int)$this->repeatCounter; $Cnt++) {
        print ($this->displayPreTag ? "<pre>\n" : "");
        eval($script);
        print ($this->displayPreTag ? "</pre>\n" : "");
        // if($happendException == true) {
        //   break;
        // }
      }
      $EndTime = microtime(true);
      $TotalTime = $EndTime - $StartTime;
      print "\n<br /><b>" . sprintf("%f", $TotalTime) . "秒(" . $this->limitTime . ")</b>\n";
      if($TotalTime > 60) {
        print sprintf("<br /><b>%.4f分</b>\n", $TotalTime / 60);
      }
    }
  }

  private function _execute($mode) {
    if(is_null($mode) OR empty($mode)) {
      $this->_execute_eval();
      return;
    }
    switch($mode) {
      case "phpinfo":
        phpinfo();
        return;

      case "server" :
        echo '<h2>$_SERVER' . "</h2>\n";
        $this->writeTable($_SERVER);
        return;

      case "indent" :
        // インデント付与の場合
        if(strlen($this->receive_strings) ==  0) {
          return;
        }
        $outputText = "";
        $indentSize = $this->indentSize;
        if($indentSize < 1) {
          return;
        }
        $script = trim(stripslashes($this->receive_strings));
        $script = mb_htmlentity($script);
        foreach(explode("\n", preg_replace(array("/\r?\n/", "/<br( \/)?>\n?/i"), "\n", $script)) as $Line) {
          $outputText .= str_repeat("&nbsp;", $indentSize) . rtrim($Line) . "\n";
        }
        echo "<textarea rows='20' cols='' class='form-control' onFocus='this.select()'>{$outputText}</textarea><br />\n";
        return;
      case "strlen" :
        echo "<input type='textbox' style='width:90%;' onFocus='this.select();' value='" . strlen($this->receive_strings) . "' />\n";
        if(strpos($this->receive_strings, "\r\n") !== false) {
          print "<p>&yen;r&yen;n込み</p>";
        } else if(strpos($this->receive_strings, "\n") !== false) {
          print "<p>&yen;n込み</p>";
        }
        return;
      case "bin2hex" :
        print '<textarea rows="20" class="form-control" onFocus="this.select();">' . bin2hex($this->receive_strings) . "</textarea>\n";
        return;
      case "baseconvert16" :
        print '<textarea rows="20" class="form-control" onFocus="this.select();">' . base_convert($this->receive_strings, 10, 16) . "</textarea>\n";
        return;
      case "baseconvert10" :
        print '<textarea rows="20" class="form-control" onFocus="this.select();">' . base_convert($this->receive_strings, 16, 10) . "</textarea>\n";
        return;
    }

  }

  public function writeTable($arrays) {
    echo "<table class='tbl' cellspacing='0' cellpadding='0'>\n";
    foreach($arrays as $key => $value) {
      echo "<tr><th>{$key}</th><td>" . mb_debughtmlentity($value) . "</td></tr>\n";
    }
    echo "</table>\n";
  }
}
// 実行
$debuger = new PhpDebug();

?>
<form name="_form" method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
  <div>
    <input type="hidden" name="mode" value="" />
    <textarea rows="20" cols="" class="form-control tabIndent" name="exescript"><?php echo $debuger->receive_strings ?></textarea><br />
    <input type="button" class="btn btn-primary" name="exe" value="実行" onclick="_form.submit();" />
    <input type="button" class="btn btn-info" name="reset" value="クリア" />
    実行回数<input type="text" class="form-control" name="counter" value="<?php echo $debuger->repeatCounter ?>" size="10" />回
    最大待機時間<input type="text" class="form-control" name="limittime" value="<?php echo $debuger->limitTime ?>" size="6" maxlength="3" />秒　
    <label><input type="checkbox" name="pre" value="preon" <?php echo ($debuger->displayPreTag ? "checked" : "") ?>/>&lt;pre&gt;をつける</label>
  </div>
  <div>
    <input type="button" class="btn btn-success" name="strlen" value="strlen" onclick="_form.mode.value='strlen';_form.submit();" />
    <input type="button" class="btn btn-success" name="bin2hex" value="bin2hex" onclick="_form.mode.value='bin2hex';_form.submit();" />
    <input type="button" class="btn btn-success" name="base_convert10-&gt;16" value="base_convert10-&gt;16" onclick="_form.mode.value='baseconvert16';_form.submit();" />
    <input type="button" class="btn btn-success" name="base_convert16-&gt;10" value="base_convert16-&gt;10" onclick="_form.mode.value='baseconvert10';_form.submit();" />
    <input type="button" class="btn btn-success" name="phpinfo" value="PHPINFO" onclick="_form.mode.value='phpinfo';_form.submit();" />
    <input type="button" class="btn btn-success" name="server" value="server" onclick="_form.mode.value='server';_form.submit();" />
  </div>
  <div class="radio">
    <label><input type="radio" name="convert" value="" <?php echo (is_null($debuger->convert) ? "checked" : "") ?> />スクリプトの実行</label>　
    <label><input type="radio" name="convert" value="strtolowwer" <?php echo  ($debuger->convert == "strtolowwer" ? "checked" : "") ?> />テキストを小文字化する</label>　
    <label><input type="radio" name="convert" value="strtoupper" <?php echo ($debuger->convert == "strtoupper" ? "checked" : "") ?> />テキストを大文字化する</label>　
    <label><input type="radio" name="convert" value="mb_convert_hankaku" <?php echo ($debuger->convert == "mb_convert_hankaku" ? "checked" : "") ?> />テキストを半角化する</label>　
    <label><input type="radio" name="convert" value="print" <?php echo ($debuger->convert == "print" ? "checked" : "") ?> />テキストをPHPのPRINT文に変換する</label><br />
    インデントスペース数<input type="text" class="form-control" name="indentsize" value="<?php echo $debuger->indentSize ?>" size="10" maxlength="2" />
    <input type="button" class="btn btn-success" name="indent" value="インデント付与" onClick="_form.mode.value='indent';_form.submit();" />
  </div>
<hr />
<div class="alert alert-dismissible alert-info">
<?php

$debuger->execute();

if(!is_null($debuger->convert)) {
  echo "<pre>\n";
  if($debuger->convert == "strtolowwer") {
    echo '<textarea rows="20" cols="" style="width:90%;height:40%;" >' . strtolower($debuger->receive_strings) . "</textarea><br />\n";
  } else if($debuger->convert == "strtoupper") {
    echo '<textarea rows="20" cols="" style="width:90%;height:40%;" >' . strtoupper($debuger->receive_strings) . "</textarea><br />\n";
  } else if($debuger->convert == "mb_convert_hankaku") {
    // 入力文字列を半角化の場合
    echo '<textarea rows="20" cols="" style="width:90%;height:40%;" >' . mb_convert_kana($debuger->receive_strings, "as") . "</textarea><br />\n";
  } else if($debuger->convert == "print") {
    // テキストをPHPのPRINT文に変換する
    $text = preg_replace("/\r?\n/", "\n", $debuger->receive_strings);
    echo '<textarea rows="20" cols="" style="width:90%;height:40%;" onFocus="this.select()">';
    foreach(explode("\n", $text) as $line) {
      echo 'print "' . htmlspecialchars(str_replace("'", "\\'", $line)) . '"<br />';
    }
    echo "</textarea><br />\n";
  }
  echo "</pre>\n";
} else {
  echo '<br />'. date("Y/m/d H:i:s") . "<br />\n";
}
?>
<br id="bottom" />
</div>
</form>
</body>
</html>
