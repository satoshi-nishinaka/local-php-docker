<?php

// 共通的な関数
error_reporting(E_ALL | E_STRICT);
//error_reporting(E_STRICT);

date_default_timezone_set("Asia/Tokyo");

/**
 * マルチバイトに対応したhtmlentity関数
 * ※文字列が長い場合は処理に時間がかかります！！
 *
 * 文字コードについて http://ash.jp/code/code.htm
 *
 * @param string $text マルチバイトを含んだ文字列
 * @param boolean $option 文字コードを表示するか
 *
 * @return string htmlentity化した文字列
 *
 */
function mb_htmlentity($text, $option = false) {
  if (!is_string($text)) {
    // 引数が文字列でない場合は空文字列を返却する
    return "";
  }
  // 文字コードの判定をmb_check_encodingで行う
  $encoding = mb_detect_encoding($text, "auto", true);
  if ($option) {
    print "<p>" . $encoding . "</p>";
  }
  // 文字列の長さをmb_strlenで行う
  $textLength = mb_strlen($text);

  // 加工後の文字列
  $convertedText = "";

  for ($cnt = 0; $cnt < $textLength; $cnt++) {
    // 一文字ずつチェックを行う
    $character = mb_substr($text, $cnt, 1);

    switch ($encoding) {
      case "SJIS" :
        // Shift JISの場合、半角カナは1バイト
        $convertedText .= ((strlen($character) != 2 && !(0xA1 <= ord($character) && ord($character) <= 0xDF)) ? htmlentities($character, ENT_NOQUOTES) : $character);
        break;
      case "UTF-8" :
      case "EUC-JP" :
      default :
        $convertedText .= ((strlen($character) != 2 ) ? htmlentities($character, ENT_NOQUOTES) : $character);
        break;
    }
  }

  // タブ、スペース、改行を任意のHTMLエンティティに置換する
  return preg_replace(
    array(
    "/\t/",
    "/ /",
    "/\r?\n/"), array(
    str_repeat("&nbsp;", 4),
    "&nbsp;",
    "<BR />\n"), $convertedText);
}

/**
 * aaa用マルチバイトに対応したhtmlentity関数
 *
 * @param mixed $text マルチバイトを含んだ文字列
 * @return string htmlentity化した文字列
 */
function mb_debughtmlentity($text): string
{
  if (gettype($text) == "boolean") {
    return ($text === true) ? "<b>TRUE</b>" : "<b>FALSE</b>";
  }
  if (gettype($text) !== "string") {
    return $text;
  }
  $textLength = mb_strlen($text);
  $convertedText = "";
  for ($cnt = 0; $cnt < $textLength; $cnt++) {
    $character = mb_substr($text, $cnt, 1);

    $convertedText .= ((strlen($character) != 2 && !(0xA1 <= ord($character) && ord($character) <= 0xDF)) ? htmlentities($character, ENT_NOQUOTES) : $character);
  }
  return preg_replace(
    ["/ /", "/\t/", "/\r?\n/"],
    ['<font color="red">_</font>', '<font color="orange">____</font>', '<font color="orange"></font><BR />'],
    $convertedText
  );
}

function aaa($assoc) {
  assoc_table($assoc, 1);
  //exit;
}

function aaa1($assoc) {
  assoc_table($assoc, 0);
}

function aaa2($assoc) {
  return assoc_table($assoc, 0, TRUE);
}

function assoc_table($assoc, $exit = 1, $no_print = FALSE) {
   if (is_string($assoc) || $assoc == "") {
     # 文字列として処理
     $assoc = array(
       array(
         "<span style=\"color:#AA55AA;\">文字列</span>" => $assoc
       )
     );
   }


  if (is_resource($assoc)) {
    $leng = mysql_num_rows($assoc);
    $all = array();
    for ($i = 0; $i < $leng; $i++) {
      $all[] = mysql_fetch_assoc($assoc);
    }
    $assoc = $all;
  }
  if (count($assoc) == 0) {
    $assoc = array($assoc);
  }
  $leng = count($assoc);
  $keys = array();
  foreach ($assoc as $key1 => $value1) {
    if (!is_array($value1)) {
      $value1 = array("<font color='green'>変数</font>" => $value1);
      $assoc[$key1] = $value1;
    }
    foreach ($value1 as $key => $value) {
      $keys[$key] = $key;
    }
  }

  $th = "<tr bgcolor='#BBBBBB'>\n";
  $th .= "<th nowarp>&nbsp;</th>\n";
  foreach ($keys as $key => $value) {
    $th .= "<th nowarp>" . $key . "</th>\n";
  }
  $th .= "</tr>\n";

  $table = "<table border='1' cellspacing='0' cellpadding='3' style='font-size: 12px'>\n" . $th;
  $i = 0;
  foreach ($assoc as $key1 => $value1) {
    $table .= "<tr bgcolor='" . ( ($i++) % 2 ? "#EEEEEE" : "#DDDDDD") . "' valign='top'>\n";
    $table .= "<td>" . $key1 . "</td>\n";
    foreach ($keys as $key => $value) {
      $table .= "<td nowrap>" .
        (@strlen($value1[$key]) ?
          (is_array($value1[$key]) || is_object($value1[$key]) ? aaa2($value1[$key]) : mb_debughtmlentity($value1[$key])) :
          "&nbsp;"
        ) . "</td>\n";
    }
    $table .= "</tr>\n";
  }
  $table .= "</table>\n";
  if ($no_print) {
    return $table;
  }
  else {
    print $table;
  }

  if ($exit) {
    //exit;
  }
}

// HTTPリクエストの時だけ有効にする
if(isset($_SERVER['SERVER_PROTOCOL']) && defined('ERROR_HANDLER_ON') && ERROR_HANDLER_ON) {

  if(!function_exists('personal_debug_error_handler')) {
    function personal_debug_error_handler($errorNumber, $errorText, $errorFile, $errorContext) {
      echo __FUNCTION__ . '<br />';
      echo $errorText . '<br />';
      echo $errorFile . " " . $errorNumber . '<br />';
      echo '<pre>';
      aaa(debug_backtrace());
      echo '<pre>';
    }

    set_error_handler('personal_debug_error_handler');
  }

  set_time_limit(0);
}

///////////////////////////////////////////////////////////////////////////////
// referer: http://uzulla.hateblo.jp/entry/2013/12/20/041619
// ini_set("display_errors", 0);
// ini_set("display_startup_errors", 0);
// NoticeやDeprecated含めて全部のエラーがほしい
error_reporting(E_ALL);
register_shutdown_function(function() {
  $e = error_get_last();
  if (isset($e['type']) && (
      $e['type'] == E_ERROR ||
      $e['type'] == E_PARSE ||
      $e['type'] == E_CORE_ERROR ||
      $e['type'] == E_COMPILE_ERROR ||
      $e['type'] == E_USER_ERROR)) {
    $body = "致命的なエラーが発生しました。\n" .
      "Error type:\t {$e['type']}\n" .
      "Error message:\t {$e['message']}\n" .
      "Error file:\t {$e['file']}\n" .
      "Error line:\t {$e['line']}\n" .
      "\n\n" .
      // printf($e, 1) . "\n\n" .
      "HostName: " . gethostname() . "\n";
    $headers =
      'From: satoshie.sp@gmail.com' . "\r\n" .
      'Reply-To: satoshie.sp@gmail.com' . "\r\n" .
      'X-Mailer: PHP/' . phpversion();
    mail('satoshie.sp@gmail.com', "Error occurred.", $body, $headers);
  }
});

set_error_handler(function($errno, $errstr, $errfile, $errline) {
  throw new ErrorException($errstr, $errno, 0, $errfile, $errline);
});

