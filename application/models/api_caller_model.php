<?php
class Api_caller_model extends CI_Model
{
    private $headers;
    private $_CI;

    public function __construct()
    {
        define('DECODE', 1);
        define('NO_DECODE', 0);
        $this->_CI = &get_instance();
    }

    /**
     * 顯示執行 API 回來的結果
     *
     * @param string  $urls            自訂的 class 及 function 名稱
     * @param array   $datas           額外的參數
     * @param string  $country         國別
     * @param string  $host            主機名稱
     * @param string  $env             dev/beta/local/priview
     * @param boolean $new_evnironment 判斷是否為新環境, default = TRUE
     * @return array
     */
    public function show_api($urls, $datas, $env)
    {
         $country = $host = "";


        $url = $this->_get_api_url($host, $urls, $env);

        //讀取並判斷是否有設定各環境的參數
        $data = $this->_get_env_params($datas, $env);

        //Load site headers setting
        // $this->headers = $this->_get_site_header_setting($country, $env);

        $temp['title'] = "輸入參數：";
        $temp['param'] = $this->print_r_fix($data, TRUE);

        $temp['env_class'] = $env;
        // $temp['env_info'] = "呼叫 [ " . $env . " ] 環境: 的 [ " . $host ." ] Api:: 國家: " . $country ;
        $temp['env_info'] = "呼叫 [ " . $env . " ] ";

        $temp['json_title'] = "參數的 JSON 格式：";
        $temp['json_param'] = "<div>" .json_encode($data, TRUE) . "</div>";

        $temp['start'] = "====  呼叫開始  ====";
        $this->benchmark->mark('call_api');
        $ret = $this->call_api($url, $data, 0);
        $ret_info = $this->get_ret_info($ret);
        $clear_json  = $ret_info['clear_json'];
        $prefix_info = $ret_info['prefix_info'];
        fb_log(json_decode($clear_json));
        $temp['times'] = "執行時間：<b> " . $this->runtime_format($this->benchmark->elapsed_time('call_api')) . "</b><br>";
        $temp['result'] = $this->print_r_fix(json_decode($clear_json, true), TRUE);
        if ($ret != $clear_json)
        {
            $temp['result'] = "<div style='color:#f00; margin-bottom:20px;'>警告: 目前 API 有使用 echo 直接輸出額外的 DEBUG 資訊，完工前需拿掉!</div>"
                              . "<div style='border-top:1px dashed #ccc; margin:10px 0; padding:10px 0; border-bottom:1px dashed #ccc; margin:10px 0; padding:10px 0;'><pre>$prefix_info</pre></div>"
                              . $temp['result'];
        }
        $temp['origin'] = "原始訊息：";
        $temp['mesg'] = $this->_convert_ret($ret);
        $temp['end'] = "====  呼叫完畢  ====";
        $temp['api_form'] = "";
        $temp['api_url'] = $url;
        $temp['API_NAME'] = "{$this->_CI->call_function} [{$this->_CI->call_env}]";
        $this->parser->parse('api_result_view', $temp);

        return json_decode($clear_json, true);
    }

    /**
     * 重組 API 路徑
     *
     * @param type $host_name
     * @param type $fn_name
     * @param type $env
     * @return type
     */
    private function _get_api_url($host_name, $fn_name, $env)
    {
        switch ($env)
        {
            case Api_caller::LOCAL_IRS1:
                $api_url = "http://irs/{$fn_name}";
                break;
            case Api_caller::LOCAL_IRS2:
                $api_url = "http://irs2/{$fn_name}";
                break;
        }
        return $api_url;

    }

    /**
     * 讀取各主機環境自行設定的參數
     *
     * @param array  $datas
     * @param string $env
     * @return array
     */
    private function _get_env_params(array $datas, $env)
    {
        if (isset($datas[$env]))
        {
            return $datas[$env];
        }

        return $datas;
    }

    /**
     * 讀取 site header 及 proxy 設定檔
     *
     * @param type $country 國別 tw/sg
     * @param type $env     環境 local/dev/beta/preview
     * @return array
     */
    private function _get_site_header_setting($country, $env)
    {
        $lib_name = 'site_header_' . $country . '_lib';
        $this->load->library($lib_name);
        return $this->$lib_name->$env();
    }

    private function _get_domain($env)
    {
        switch ($env)
        {
            case Api_caller::DEV:
                return '.dev1.ux';
            case Api_caller::BETA:
                return '.beta1.ux';
            case Api_caller::PREVIEW:
            case Api_caller::ONLINE:
                return '.idc1.ux';
            default :
                return 'localhost';
        }
    }

    /**
     * 取得本機各主機對應的 port
     *
     * @param type $host_name
     */
    private function _get_local_port($host_name)
    {
        switch ($host_name)
        {
            case Api_caller::HOST_PMADMIN_API:
                return ':9001';
            case Api_caller::HOST_WWW_API:
                return ':9002';
            case Api_caller::HOST_SEARCH_API:
                return ':9003';
            case Api_caller::HOST_VENDOR_API:
                return ':9004';
            default:
                return ':9001';
        }
    }

    public function call_api($url, $data = [], $decode = 0)
    {
        $ret = $this->api_lib->curl_post ($url, $data);
        return ($decode == 0) ? $ret : json_decode($ret);
    }

    public function get_ret_info($str)
    {
        $pos = -1;
        do
        {
            $pos = strpos($str, '{', $pos+1);
            $json = substr($str, $pos);
            if (json_decode($json)) break;
        } while ($pos !== FALSE);

        $ret = array
        (
            'prefix_info' => htmlspecialchars(substr($str, 0, $pos), ENT_QUOTES),
            'clear_json'  => substr($str, $pos)
        );

        return $ret;
    }

    private function _convert_ret($str)
    {
        if (strpos($str, 'Fatal error') !== FALSE)
        {
            $str = str_replace("<font size='1'>", "<font size='4'>", $str);
        }
        return $str;
    }

    public function runtime_format($sec)
    {
        if ($sec < 1)
        {
            $ms_sec = intval($sec * 1000);
            return "$ms_sec ms.";
        }
        else
        {
            return "$sec Seconds.";
        }
    }

    /**
     * 修正 print_r 無法正常顯示布林值的問題
     * 直接把布林值取代成字串
     * @author Leo.Kuo
     */
    public function print_r_fix($value, $flag=FALSE)
    {
        $fixed_value = $this->fix_print_r_boolean($value);
        if ($flag)
            return print_r($fixed_value, $flag);
        else
            print_r($fixed_value);
    }

    public function fix_print_r_boolean($value)
    {
        if (is_array($value))
        {
            foreach ($value as $key => $v)
            {
                $value[$key] = $this->fix_print_r_boolean($v);
            }
        }
        else if (is_object($value))
        {

            foreach ($value as $key => $v)
            {
                $value->$key = $this->fix_print_r_boolean($v);
            }
        }
        else if (gettype($value) == "boolean")
        {
            $flag = ($value) ? "True" : "False";
            $value = "$flag (Boolean)";
        }

        return $value;
    }
}

