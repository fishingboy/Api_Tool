<?php
$conf = new text_editor('C:\xampp\apache\conf\httpd.conf');
if (!$conf->is_exists_text('/httpd-vhosts\*/'))
{
    $conf->replace_text('Include "conf/extra/httpd-vhosts.conf"', 'Include "conf/extra/httpd-vhosts*.conf"');
    echo "Apache conf edit OK!";
}

/**
 * 檔案編輯器
 */
class text_editor
{
    var $file;
    var $fp;

    public function __construct($file)
    {
        $this->file = $file;
    }

    /**
     * 判斷字串是否存在
     */
    public function is_exists_text($str)
    {
        $fp = fopen($this->file, "r");
        while (FALSE !== ($line = fgets($fp)))
        {
            if (preg_match($str, $line))
            {
                return TRUE;
            }
        }
        return FALSE;
    }

    /**
     * 插入文字到最後
     */
    public function append_end($str)
    {
        $fp = fopen($this->file, "a");
        fwrite($fp, "\n" . $str);
        fclose($fp);
        return $this;
    }

    /**
     * 取代文字
     */
    public function replace_text($str, $str2)
    {
        $content = file_get_contents($this->file);
        $content = str_replace($str, $str2, $content);

        $fp = fopen($this->file, "w");
        fwrite($fp, $content);
        fclose($fp);
        return $this;
    }
}
