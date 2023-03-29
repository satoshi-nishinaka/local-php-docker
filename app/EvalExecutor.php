<?php

declare(strict_types=1);

class EvalExecutor
{
    public int $repeatCounter = 1;
    public int $limitTime = 30;
    public bool $usePreTag = false;
    public string $convert = 'eval';
    public string $receive_strings = '';
    public int $indentSize = 0;

    public function __construct()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }

        $this->initialize();
    }

    public function execute(): EvalExecutor
    {
        $this->_execute(filter_input(INPUT_POST, 'mode'));
        return $this;
    }

    public function output(): void
    {
        $buffer = [];
        if ($this->convert !== 'eval') {
            $buffer[] = '<pre>';
            switch ($this->convert) {
                case 'strtolowwer':
                    $buffer[] = '<textarea rows="20" cols="" style="width:90%;height:40%;" >' . strtolower($this->receive_strings) . '</textarea><br />';
                    break;
                case  'strtoupper':
                    $buffer[] = '<textarea rows="20" cols="" style="width:90%;height:40%;" >' . strtoupper($this->receive_strings) . '</textarea><br />';
                    break;
                case 'mb_convert_hankaku':
                    // 入力文字列を半角化の場合
                    $buffer[] = '<textarea rows="20" cols="" style="width:90%;height:40%;" >' . mb_convert_kana($this->receive_strings, "as") . '</textarea><br />';
                    break;
                case 'print':
                    // テキストをPHPのPRINT文に変換する
                    $text = preg_replace("/\r?\n/", "\n", $this->receive_strings);
                    $buffer[] = '<textarea rows="20" cols="" style="width:90%;height:40%;" onFocus="this.select()">';
                    foreach (explode("\n", $text) as $line) {
                        $buffer[] = 'echo "' . htmlspecialchars(str_replace("'", "\\'", $line)) . '"<br />';
                    }
                    $buffer[] = '</textarea><br />';
                    break;
            }
            $buffer[] = '</pre>';
        } else {
            $buffer[] = '<br />' . date('Y/m/d H:i:s') . '<br />';
        }

        echo implode("\n", $buffer);
    }

    /**
     * 初期化する
     *
     * @return void
     */
    private function initialize(): void
    {
        // 処理繰り返し回数
        $counter = filter_input(INPUT_POST, 'counter');
        if (!is_null($counter)) {
            $this->repeatCounter = max(intval($counter), 1);
        }
        // 実行時間制限
        $limitTime = filter_input(INPUT_POST, 'limittime');
        if (!is_null($limitTime)) {
            $this->limitTime = intval($limitTime) < 600 ? 30 : intval($limitTime);
        }
        // preタグで囲んで実行結果を表示するか？
        $usePreTag = filter_input(INPUT_POST, 'pre');
        if (!is_null($usePreTag)) {
            $this->usePreTag = true;
        }
        $convert = filter_input(INPUT_POST, 'convert');
        if (!is_null($convert) && 0 < strlen($convert)) {
            $this->convert = $convert;
        }
        set_time_limit($this->limitTime);

        // POSTされた文字列を格納
        $this->receive_strings = filter_input(INPUT_POST, 'exescript');

        $indentSize = filter_input(INPUT_POST, 'indentsize');
        if (is_numeric($indentSize)) {
            $this->indentSize = intval($indentSize);
        }
    }

    private function executeEval(): void
    {
        $buffer = [];
        $script = preg_replace("/(^<\?(php)?)|(\?>$)/", "", trim($this->receive_strings));

        if (strlen($script) != 0 && !str_ends_with($script, ';')) {
            $script .= ';';
        }

        if ($script) {
            $startTime = microtime(true);
            for ($i = 0; $i < $this->repeatCounter; $i++) {
                $buffer[] = ($this->usePreTag ? "<pre>\n" : "");
                try {
                    eval($script);
                } catch (\Exception $e) {
                    $buffer = array_merge($buffer, [
                        '<pre>',
                        $e->getMessage(),
                        '',
                        '</pre>',
                    ]);
                }
                $buffer[] = ($this->usePreTag ? "</pre>\n" : "");
            }
            $endTime = microtime(true);
            $totalTime = $endTime - $startTime;
            $buffer[] = '<br /><hr />';
            $buffer[] = '<b>' . sprintf('%f', $totalTime) . '秒(' . $this->limitTime . ')</b>';
            if ($totalTime > 60) {
                $buffer[] = sprintf("<br /><b>%.4f分</b>\n", $totalTime / 60);
            }
        }

        echo implode("\n", $buffer);
    }

    private function writeTable(array $list): void
    {
        $buffer[] = '<table class=\'tbl\'>';
        foreach ($list as $key => $value) {
            $buffer[] = '<tr>';
            $buffer[] = "<th>{$key}</th>";
            $buffer[] = '<td>' . $this->mbDebugHtmlEntity($value) . '</td>';
            $buffer[] = '</tr>';
        }
        $buffer[] = "</table>\n";

        echo implode("\n", $buffer);
    }

    private function _execute($mode): void
    {
        switch ($mode) {
            case 'eval':
                $this->executeEval();
                return;

            case 'phpinfo':
                phpinfo();
                return;

            case 'server' :
                echo '<h2>$_SERVER' . "</h2>\n";
                echo '<pre>';
                $this->writeTable($_SERVER);
                echo '</pre>';
                return;

            case 'indent' :
                // インデント付与の場合
                if (strlen($this->receive_strings) == 0) {
                    return;
                }
                $outputText = "";
                $indentSize = $this->indentSize;
                if ($indentSize < 1) {
                    return;
                }
                $script = trim(stripslashes($this->receive_strings));
                $script = mbHtmlEntity($script);
                foreach (explode("\n", preg_replace(array("/\r?\n/", "/<br( \/)?>\n?/i"), "\n", $script)) as $Line) {
                    $outputText .= str_repeat('&nbsp;', $indentSize) . rtrim($Line) . "\n";
                }
                echo "<textarea rows='20' cols='' class='form-control' onFocus='this.select()'>{$outputText}</textarea><br />\n";
                return;
            case 'strlen' :
                echo "<input type='textbox' style='width:90%;' onFocus='this.select();' value='" . strlen($this->receive_strings) . "' />\n";
                if (str_contains($this->receive_strings, "\r\n")) {
                    echo "<p>&yen;r&yen;n込み</p>";
                } else if (strpos($this->receive_strings, "\n") !== false) {
                    echo "<p>&yen;n込み</p>";
                }
                return;
            case "bin2hex" :
                echo '<textarea rows="20" class="form-control" onFocus="this.select();">' . bin2hex($this->receive_strings) . "</textarea>\n";
                return;
            case "baseconvert16" :
                echo '<textarea rows="20" class="form-control" onFocus="this.select();">' . base_convert($this->receive_strings, 10, 16) . "</textarea>\n";
                return;
            case "baseconvert10" :
                echo '<textarea rows="20" class="form-control" onFocus="this.select();">' . base_convert($this->receive_strings, 16, 10) . "</textarea>\n";
                return;
        }
    }

    private function mbDebugHtmlEntity($value): string
    {
        if (is_bool($value)) {
            return $value ? 'TRUE' : 'FALSE';
        }
        if (is_numeric($value)) {
            return (string)$value;
        }

        if (is_array($value)) {
            $buffer = [];
            foreach ($value as $k => $v) {
                $buffer[] = "{$k}: " . $this->mbDebugHtmlEntity($v) . "<br />";
            }

            return implode("\n", $buffer);
        }

        if (!$value) {
            return "<b>empty</b>";
        }
        return $value;
    }
}

function customErrorHandler(int $errno, string $errstr, string $errfile, int $errline, array $errcontext):bool
{
    echo $errstr;
    return true;
}
