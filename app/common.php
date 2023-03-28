<?php

// 共通的な関数
error_reporting(E_ALL | E_STRICT);
//error_reporting(E_STRICT);

date_default_timezone_set("Asia/Tokyo");
if (!defined('UA_IE')) {
  define("UA_IE", "Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.0)");
}
if(!defined('UA_CHROME')) {
  define("UA_CHROME", "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/31.0.1650.63 Safari/537.36");
}
if(!defined("UA_IPHONE")) {
  define("UA_IPHONE", "Mozilla/5.0 (iPhone; CPU iPhone OS 9_3_2 like Mac OS X) AppleWebKit/601.1.46 (KHTML, like Gecko) Version/9.0 Mobile/13F69 Safari/601.1");
}
if (!function_exists('easyCurlMulti')) {
  function easyCurlMulti($urls, $userAgent = NULL, $options = NULL) {
    if (is_null($userAgent)) {
      if (isset($_SERVER['HTTP_USER_AGENT']) == FALSE) {
        $userAgent = UA_CHROME;
      } else {
        $userAgent = $_SERVER['HTTP_USER_AGENT'];
      }
    }
    // マルチ cURL ハンドルを作成します
    $mh = curl_multi_init();
    $channels = array();
    foreach ($urls as $url) {
      if(!is_string($url)) {
        continue;
      }
      // 新規 cURL リソースを作成します
      $channel = curl_init();

      // URL や他の適当なオプションを設定します
      curl_setopt($channel, CURLOPT_URL, trim($url, '"\''));

      curl_setopt($channel, CURLOPT_USERAGENT, $userAgent);
      curl_setopt($channel, CURLOPT_RETURNTRANSFER, TRUE);

      // ハンドルを追加します
      curl_multi_add_handle($mh, $channel);
      $channels[$url] = $channel;
    }

    $running = NULL;

    // ハンドルを実行します
    do {
      curl_multi_exec($mh, $running);
    } while (0 < $running);

    $response = array();
    foreach ($channels as $url => $channel) {
      $statusCode      = curl_getinfo($channel, CURLINFO_HTTP_CODE);
      $responseContents  = curl_multi_getcontent($channel);
      if(isset($options['withInStatusCode'])) {
        $response[$url] = array('statusCode' => $statusCode, 'contents' => $responseContents);
      } else {
        $response[$url] = $responseContents;
      }
      curl_multi_remove_handle($mh, $channel);
    }
    curl_multi_close($mh);

    return $response;
  }
}

if (!function_exists('easyCurl')) {

  function easyCurl($url, $userAgent = NULL) {
    $channel = curl_init();
    curl_setopt($channel, CURLOPT_URL, $url);
    curl_setopt($channel, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($channel, CURLOPT_FOLLOWLOCATION, TRUE);
    curl_setopt($channel, CURLOPT_MAXREDIRS, 5);
    curl_setopt($channel, CURLOPT_HTTPHEADER, array("User-Agent: " . (is_null($userAgent) ? UA_CHROME : $userAgent)));
    $response = curl_exec($channel);
    $statusCode = curl_getinfo($channel, CURLINFO_HTTP_CODE);
    if ($statusCode != 200) {
      return FALSE;
    }
    return $response;
  }

}

/**
 * URLのストリングをファイルとして使用可能な文字列に変換する
 *
 * @param string $url URL文字列
 *
 * @return string 置換後の文字列
 *
 */
if (!function_exists('convertURL')) {

  function convertURL($url) {
    $text = preg_replace("%[_]{2,}%", "_", preg_replace("%[:/?&=|]%", "_", $url));
    if (255 < strlen($text)) {
      //$parsed = parse_url($url);
      //$returnString = date("YmdHis") . sha1($returnString) . convertURL($parsed['path']);
      return sha1($text);
    }
    return $text;
  }

}

/**
 * ２ちゃんのようにURLの指定が不十分な場合にURL文字列を補完する
 *
 * @param string $url リクエストURL
 *
 * @return string 補完後のURL
 *
 */
if (!function_exists('supplementURL')) {

  function supplementURL($url) {
    if ($url != "" && startwith($url, "http") === FALSE) {
      // httpから始まらない場合、補完を行う
      if (startwith($url, "tp")) {
        $url = "ht" . $url;
      } else if (startwith($url, "ttp")) {
        $url = "h" . $url;
      } else {
        // 上記に該当しない場合エラー
        trigger_error("URL String Error", E_USER_WARNING);
        return "";
      }
    }
    return $url;
  }

}

/**
 * file_get_contentsのURL指定用
 *
 * @param string $url 取得URL
 * @param string $refererUrl RegererのURL
 *
 */
if (!function_exists('getContents')) {

  function getContents($url, $refererUrl = "", $userAgent = NULL) {
    if ($url == "") {
      return "";
    }
    // バッファ開始
    ob_start();
    // HTTPリクエストヘッダを捏造
    $options = array(
      'http' => array(
        'method' => "GET",
        'header' =>
        "Accept-language: ja\r\n" .
        "User-Agent: " . (is_null($userAgent) ? UA_CHROME : $userAgent) . "\r\n" .
        "Referer: {$refererUrl}"
      )
    );

    $context = stream_context_create($options);
    $contentsText = file_get_contents($url, TRUE, $context);
    // バッファをクリア
    ob_clean();
    return $contentsText;
  }

}

if (!function_exists('startwith')) {

  function startwith($haystack, $needle) {
      return substr($haystack, 0, strlen($needle)) === $needle;
  }

}
/**
 * startwithのエイリアスとして定義
 * どちらでも使えるように
 */
if (!function_exists('startWith')) {

  function startWith($haystack, $needle) {
    return startwith($haystack, $needle);
  }

}
if (!function_exists('endwith')) {

  function endwith($haystack, $needle) {
    if (mb_strlen($haystack) < mb_strlen($needle)) {
      return FALSE;
    }
    return (substr($haystack, -mb_strlen($needle)) == $needle);
  }

}
/**
 * endwithのエイリアスとして定義
 * どちらでも使えるように
 */
if (!function_exists('endWith')) {

  function endWith($haystack, $needle) {
    return endwith($haystack, $needle);
  }

}

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
function mb_htmlentity($text, $option = FALSE) {
  if (!is_string($text)) {
    // 引数が文字列でない場合は空文字列を返却する
    return "";
  }
  // 文字コードの判定をmb_check_encodingで行う
  $encoding = mb_detect_encoding($text, "auto", TRUE);
  if ($option === TRUE) {
    print "<p>" . $encoding . "</p>";
  }
  // 文字列の長さをmb_strlenで行う
  $textLength = mb_strlen($text);

  // 加工後の文字列
  $convertedText = "";

  for ($cnt = 0; $cnt < $textLength; $cnt++) {
    // 一文字ずつチェックを行う
    $charactor = mb_substr($text, $cnt, 1);

    switch ($encoding) {
      case "SJIS" :
        // Shift JISの場合、半角カナは1バイト
        $convertedText .= ((strlen($charactor) != 2 && !(0xA1 <= ord($charactor) && ord($charactor) <= 0xDF)) ? htmlentities($charactor, ENT_NOQUOTES) : $charactor);
        break;
      case "UTF-8" :
      case "EUC-JP" :
      default :
        $convertedText .= ((strlen($charactor) != 2 ) ? htmlentities($charactor, ENT_NOQUOTES) : $charactor);
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

if (!function_exists('getURLContents')) {

  /**
   * 指定したURLのソースを取得する
   *
   * @param $url string 取得対象のURL
   * @param $refererUrl string リファラーURL。指定しない場合は空白
   */
  function getURLContents($url, $refererUrl = "", $userAgent = NULL) {
    // HTTPリクエストヘッダを捏造
    $options = array(
      'http' => array(
        'method' => "GET",
        'header' =>
        "Accept-language: ja\r\n" .
        "User-Agent: " . (is_null($userAgent) ? UA_CHROME : $userAgent) . "\r\n" .
        "Referer: {$refererUrl}"
      )
    );

    $context = stream_context_create($options);

    return file_get_contents($url, TRUE, $context);
  }

}

if (!function_exists('select')) {

  /**
   * LINQのSelectのように該当するフィールドをリストにして返す
   * 同じ処理を記述していたのでエイリアスとして使用
   * @param type $list
   * @param type $fieldName
   * @return type
   */
  function select($list, $fieldName) {
    return extractArray($list, $fieldName);
  }
}

if(!function_exists('extractArray')) {
  function extractArray($source, $targetKey) {
    $extracted = array();
    foreach ($source as $array) {
      if(!array_key_exists($targetKey, $array)) {
        continue;
      }
      if(is_array($array)) {
        $extracted[] = $array[$targetKey];
      } else if(is_object($array)) {
        $extracted[] = $array->{$targetKey};
      }

    }
    return $extracted;
  }
}

// デバッグ用関数群
function debug_aaa1($Val) {
  global $DebugMode;
  if ($DebugMode === "on") {
    aaa1($Val);
  }
}

function debug_print($val) {
  global $DebugMode;
  if ($DebugMode === "on") {
    print "<p>$val</p>";
  }
}

/**
 * aaa用マルチバイトに対応したhtmlentity関数
 *
 * @param string $text マルチバイトを含んだ文字列
 * @return string htmlentity化した文字列
 */
function mb_debughtmlentity($text) {
  if (gettype($text) == "boolean") {
    return ($text === TRUE) ? "<b>TRUE</b>" : "<b>FALSE</b>";
  }
  if (gettype($text) != "string") {
    return $text;
  }
  $textLength = mb_strlen($text);
  $convertedText = "";
  for ($cnt = 0; $cnt < $textLength; $cnt++) {
    $charactor = mb_substr($text, $cnt, 1);

    $convertedText .= ((strlen($charactor) != 2 && !(0xA1 <= ord($charactor) && ord($charactor) <= 0xDF)) ? htmlentities($charactor, ENT_NOQUOTES) : $charactor);
  }
  return preg_replace(
    array("/ /", "/\t/", "/\r?\n/"), array('<font color="red">_</font>', '<font color="orange">____</font>', '<font color="orange"></font><BR />'), $convertedText);
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

  if (is_string($assoc) && eregi("^ *select ", $assoc)) {
    # SQL文として処理
    $assoc = mysql_query($assoc);
  }
  else if (is_string($assoc) || $assoc == "") {
    # 文字列として処理
    $assoc = array(
      array(
        "<font color='#AA55AA'>文字列</font>" => $assoc
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

  if(!function_exists('parse_object')) {
    function parse_object($args, $depth = 0) {
      if (!is_array($args) && !is_object($args)) {
        MY_Log::debug(sprintf('%s%s(%s)', str_repeat("\t", $depth), $args, gettype($args)));
        return;
      }

      foreach ($args as $key => $value) {
        if (is_array($value) || is_object($value)) {
          MY_Log::debug(str_repeat("\t", $depth) . "[{$key}]");
          parse_object($value, $depth + 1);
          continue;
        }
        MY_Log::debug(str_repeat("\t", $depth) . sprintf('[%s] => %s(%s)', $key, $value, gettype($value)));
      }
    }
  }

  function headline($text) {
    $backtrace = debug_backtrace();
    MY_Log::debug(sprintf("%s[%d] %s", $backtrace[0]['file'], $backtrace[0]['line'], $text));
  }

  if (!defined('ENABLE_SQL_LOG')) {
    define('ENABLE_SQL_LOG', TRUE);
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

set_error_handler(function($errno, $errstr, $errfile, $errline){
  throw new ErrorException($errstr, $errno, 0, $errfile, $errline);
});

