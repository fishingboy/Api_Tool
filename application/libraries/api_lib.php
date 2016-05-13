<?php
/**
 * API 相關函式
 * @package Libraries
 */
class Api_lib
{
    // use error_handler_trait;

    /**
     * CI 實體
     * @var object
     */
    private $_CI;

    /**
     * API 輸出的格式
     *
     *     json        : json 格式 <br>
     *     json_pretty : 可閱讀的 json 格式 <br>
     *     return      : 直接回傳不輸出畫面 <br>
     *     print_r     : php print_r 格式 <br>
     *     var_dump    : php var_dump 格式 <br>
     * @var string
     */
    private $_format = 'json';

    public function __construct()
    {
        $this->_CI = & get_instance();
    }

    /**
     * API 結果輸出
     * @param  array      $rows           要輸出的資料
     * @param  Model_base $model_referer  model 的參考
     * @return array                      處理完的資料(如果有傳入 model 的話，加上分頁資料)
     */
    public function output($data, Model_base $model_referer = NULL)
    {
        $ret = [];

        // 狀態判斷
        $status = ($data) ? TRUE : FALSE;
        $ret['status'] = $status;

        // 輸出錯誤
        if ( ! $status)
        {
            if ($model_referer)
            {
                $msg = $model_referer->get_error();
            }
            $msg = ( ! empty($msg)) ? $msg : 'no data...';
            return $this->output_error($msg);
        }

        // 加上分頁資訊
        if (isset($model_referer))
        {
            $page_info = $model_referer->get_page_info();
            if (FALSE !== $page_info && is_array($page_info))
            {
                foreach ($page_info as $key => $value)
                {
                    $ret[$key] = $value;
                }
            }
        }

        // 資料
        if (gettype($data) == 'string')
        {
            $ret['msg'] = $data;
        }
        else
        {
            $ret['msg'] = 'OK';
            $ret['data'] = $data;
        }

        // 輸出格式
        $this->_output_format($ret);

        return $ret;
    }

    /**
     * 輸出錯誤訊息
     * @param  string $msg 錯誤訊息
     * @return void
     */
    public function output_error($msg)
    {
        $this->_output_format([
            'status' => FALSE,
            'msg'    => $msg
        ]);
    }


    /**
     * 自動切換 API 輸出格式
     * @param  string $function_name API function name
     * @return void
     */
    public function auto_switch_format($function_name)
    {
        // 判斷是否為 API 呼叫自動切換輸出格式
        $this->_format = ($this->_CI->router->method == $function_name) ? 'json' : 'return';

        // 判斷是否有強制指定 format
        $force_format = $this->_CI->input->post_get('format');
        if (isset($force_format) && $force_format)
        {
            $this->_format = $force_format;
        }
    }

    /**
     * 依格式輸出
     * @param  array $ret           資料
     * @return void
     */
    private function _output_format($ret)
    {
        // 輸出格式
        switch ($this->_format)
        {
            // json 格式
            case 'json':
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode($ret);
                break;

            // 輸出可閱讀的 json 格式
            case 'json_pretty':
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode($ret, JSON_PRETTY_PRINT);
                break;

            // php 的 print_r
            case 'print_r':
                echo "<pre>" . print_r($ret, TRUE). "</pre>";
                break;

            // php 的 var_dump
            case 'var_dump':
                var_dump($ret);
                break;
        }
    }

    /**
     * 取得 POST 參數
     * @param  array $allow_fields   允許的參數
     * @param  array $method_params  從 method 傳來的參數
     * @return array                 參數
     */
    public function get_api_params($allow_fields, $method_params = NULL)
    {
        // 所有 get post 的參數
        $get_and_post_params = array_merge($this->_CI->input->get(), $this->_CI->input->post());

        $allow_fields[] = 'format';

        $params = [];
        if (isset($method_params) && is_array($method_params))
        {
            // 檢查傳入參數
            // todo: 有時間的話可以做過 type 過瀘
            foreach ($method_params as $key => $value)
            {
                if ( ! in_array($key, $allow_fields))
                {
                    // $this->_set_error("{$this->_CI->router->class}::{$this->_CI->router->method} - API 傳入不允許的參數 [{$key}] !!");
                    return FALSE;
                }
            }
            $params = $method_params;
        }
        else
        {
            // 檢查傳入參數
            // todo: 有時間的話可以做過 type 過瀘
            foreach ($get_and_post_params as $key => $value)
            {
                if ( ! in_array($key, $allow_fields))
                {
                    // $this->_set_error("{$this->_CI->router->class}::{$this->_CI->router->method} - API 傳入不允許的參數 [{$key}] !!");
                    return FALSE;
                }
            }

            // 取得參數
            foreach ($allow_fields as $key)
            {
                $value = $this->_CI->input->get_post($key, TRUE);

                // 收集參數
                if ($value)
                {
                    $params[$key] = $value;
                }
            }
        }

        return $params;
    }

    /**
     * CURL 取得資料
     * @param  string $url   網址
     * @param  array  $data  POST 資料
     * @return string        回應內容
     */
    public function curl_post($url, $data = [])
    {
        $timeout = 600;
        $curl = curl_init($url);
        if (substr($url, 0, 5) == "https")
        {
            curl_setopt($curl, CURLOPT_PROTOCOLS, CURLPROTO_HTTPS);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 1);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        }
        else
        {
             curl_setopt($curl, CURLOPT_PROTOCOLS, CURLPROTO_HTTP);
        }
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);

        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));

        $data = curl_exec($curl);

        $response_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        // if ($response_code != "200")
        // {
            // $this->_set_error("ERROR 404!!");
            // return FALSE;
        // }
        return $data;
    }
}