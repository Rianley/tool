<?php
/**
 * 文本处理相关
 * @author SamDing
 */
namespace koboshi\tool;

class Text
{
    /**
     * 移除括号和括号中的内容
     * @param string $str
     * @param bool $recursive
     * @return string
     */
    public static function stripBrackets($str, $recursive = false)
    {
        $limit = $recursive ? -1 : 1;
        //替换 () 里面内容
        $str = preg_replace("/\(.*\)/is", "", $str, $limit);
        //替换 （） 里面内容
        $str = preg_replace("/（.*）/is", "", $str);
        //替换 [] 里面内容
        $str = preg_replace("/\[.*\]/is", "", $str, $limit);
        //替换 <> 里面内容
        $str = preg_replace("/\<.*\>/is", "", $str, $limit);
        return $str;
    }

    /**
     * 获取括号中的内容
     * @param string $str
     * @param bool $recursive
     * @return array|string
     */
    public static function grabBrackets($str, $recursive = false)
    {
        $paternA = "/\((.*)\)/is";
        $paternB = "/（(.*)）/is";
        $paternC = "/\[(.*)\]/is";
        $paternD = "/\<(.*)\>/is";
        $paternList = array($paternA, $paternB, $paternC, $paternD);
        $tmp = array();
        foreach ($paternList as $patern) {
            preg_match_all($patern, $str, $matches);
            if (isset($matches[1]) && count($matches[1]) > 0) {
                if (!$recursive) {
                    return $matches[1][0];
                }else {
                    $tmp = array_merge($tmp, $matches[1]);
                }
            }
        }
        return array_unique($tmp);
    }

    /**
     * 比较字符串，返回相似度
     * 0.0 ~ 1.0(越低越相似)
     * @param $strA
     * @param $strB
     * @param $costReplace
     * @return float
     */
    public static function compare($strA, $strB, $costReplace = 1)
    {
        $strA = self::stripSymbol($strA);
        $strB = self::stripSymbol($strB);
        $lenA = mb_strlen($strA, 'UTF-8');
        $lenB = mb_strlen($strB, 'UTF-8');
        $len = max($lenA, $lenB);
        $distance = self::levenshtein($strA, $strB, $costReplace);
        return ($distance * 1.0 / $len);
    }

    /**
     * 字符串转数组
     * @param $string
     * @param string $encoding
     * @return array
     */
    private static function string2Array($string, $encoding = 'UTF-8') {
        $arrayResult = array();
        while ($iLen = mb_strlen($string, $encoding)) {
            array_push($arrayResult, mb_substr($string, 0, 1, $encoding));
            $string = mb_substr($string, 1, $iLen, $encoding);
        }
        return $arrayResult;
    }

    private static function levenshtein($str1, $str2, $costReplace = 1, $encoding = 'UTF-8') {
        $count_same_letter = 0;
        $d = array();
        $mb_len1 = mb_strlen($str1, $encoding);
        $mb_len2 = mb_strlen($str2, $encoding);
        $mb_str1 = self::string2Array($str1, $encoding);
        $mb_str2 = self::string2Array($str2, $encoding);
        for ($i1 = 0; $i1 < $mb_len1 + 1; $i1++) {
            $d[$i1] = array();
            $d[$i1][0] = $i1;
        }
        for ($i2 = 0; $i2 < $mb_len2 + 1; $i2++) {
            $d[0][$i2] = $i2;
        }
        for ($i1 = 1; $i1 <= $mb_len1; $i1++) {
            for ($i2 = 1; $i2 <= $mb_len2; $i2++) {
                if ($mb_str1[$i1 - 1] === $mb_str2[$i2 - 1]) {
                    $cost = 0;
                    $count_same_letter++;
                } else {
                    $cost = $costReplace; //替换
                }
                $d[$i1][$i2] = min($d[$i1 - 1][$i2] + 1, //插入
                    $d[$i1][$i2 - 1] + 1, //删除
                    $d[$i1 - 1][$i2 - 1] + $cost);
            }
        }
        return $d[$mb_len1][$mb_len2];
    }

    /**
     * 移除各种符号和空格
     * @param string $str
     * @return string
     */
    private static function stripSymbol($str)
    {
        $str = trim($str);
        $oriStr = $str;
        //大小写转换，空白字符转换
        $str = strtolower($str);
        $str = str_replace(' ', '', $str);
        $str = str_replace(' ', '', $str);
        $str = str_replace("'\t'", '', $str);
        $str = str_replace("'\r\n'", '', $str);
        $str = str_replace("'\n'", '', $str);
        //删除所有符号
        $symbolList = array('`', '~', '!', '@', '#', '$', '%', '^', '&', '*', '(', ')', '-', '=', '_', '+', '[', ']', '{',
            '}', '\\', '|', ';', ':', '\'', '"', ',', '<', '.', '>', '/', '?', '·', '~', '！', '@', '#', '￥', '……',
            '（', '）', '—', '＋', '【', '】', '、', '；', '‘', '：', '“', '，', '《', '。', '》', '、', '？', '·', '「',
            '」', "”", "’", '～', '•', '▪', '＃', '…', '．');
        foreach ($symbolList as $symbol) {
            $str = str_replace($symbol, '', $str);
        }
        if (empty($str)) {
            $str = $oriStr;//纯符号字符串，保留原始
        }
        //巴比伦数字转换
        $replaceList = array('Ⅰ' => 'I', 'Ⅱ' => 'II', 'Ⅲ' => 'III', 'Ⅳ' => 'IV', 'Ⅴ' => 'V', 'Ⅵ' => 'VI', 'Ⅶ' => 'VII',
            'Ⅷ' => 'VIII', 'Ⅸ' => 'IX', 'Ⅹ' => 'X', 'Ⅺ' => 'XI', 'Ⅻ' => 'XII');
        foreach ($replaceList as $old => $new) {
            $str = str_replace($old, $new, $str);
        }
        return $str;
    }

    /**
     * 增加bom头
     * @param string $filename
     * @param string $charset
     * @return bool
     */
    public static function addBOM($filename, $charset = 'utf-8')
    {
        $charset = strtolower($charset);
        if ($charset != 'utf-8') {
            return false;
        }
        if(!file_exists($filename)) {
            return false;
        }
        $tmpFilename = $filename . '.tmp.' . md5(time());
        $rFp = fopen($filename, 'rb');
        if (!$rFp) {
            return false;
        }
        $wFp = fopen($tmpFilename, 'wb');
        fputs($wFp, "\xEF\xBB\xBF");//输出utf-8 BOM信息
        while ($chunk = fgets($rFp, 1024)) {
            fputs($wFp, $chunk);
        }
        fclose($rFp);
        fclose($wFp);
        unlink($filename);
        rename($tmpFilename, $filename);
        return true;
    }

    public static function readCsvFile($filename, callable $callback)
    {
        $fp = fopen($filename, 'rb');
        while ($row = fgetcsv($fp)) {
            $callback($row, $fp);
        }
        fclose($fp);
    }

    public static function readFile($filename, callable $callback)
    {
        $fp = fopen($filename, 'rb');
        while ($row = fgets($fp)) {
            $callback($row, $fp);
        }
        fclose($fp);
    }
}