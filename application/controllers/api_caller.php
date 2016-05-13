<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Api_caller extends CI_Controller
{
    const LOCAL_IRS1 = 'local.irs1';
    const LOCAL_IRS2 = 'local.irs2';

    private $_CI;
    public $call_function;
    public $call_env;

    public function __construct()
    {
        parent::__construct();
        $this->load->helper("firephp");
        $this->load->library('parser');
        $this->load->model("list_model");

        $this->_CI = &get_instance();
    }

    public function index()
	{
        $ignore_list = array
        (
            'index',
            'get_instance'
        );

        // Get function list
        $arr = $this->list_model->index();
        $data = array(
            'controller' => $this->_CI->router->class
        );
        $this->get_foreach($data, $arr, $ignore_list);
        $this->parser->parse('api_caller_view', $data);
	}

    public function result($function, $env)
    {
        $this->call_function = strtoupper($function);
        $this->call_env      = strtoupper($env);
        $this->list_model->$function($env);
    }

    /**
     * 迴圈處理 function list
     *
     * @param type $data
     * @param type $arr
     * @param type $ignore_list
     */
    private function get_foreach(&$data, $arr, $ignore_list)
    {
        // 排序檢查
        $this->_get_list_sort($arr);

        foreach ($arr as $method)
        {
            if (!in_array($method, $ignore_list) && preg_match("/^[^_]/", $method))
            {
                $data['tmp'][] = array(
                    'title' => '<li>
                    <a class="local"   href="/api_caller/result/' . $method . '/' . self::LOCAL_IRS1 . '" target="_blank">Local IRS1</a>
                    <a class="local"     href="/api_caller/result/' . $method . '/' . self::LOCAL_IRS2 . '" target="_blank">Local IRS2</a>
                    <span>' . $method . '</span>
                    </li>'
                );
            }
        }
    }

    /**
     * 將陣列資料排序
     *
     * return void
     */
    private function _get_list_sort(array &$sort_data)
    {
        $list_sort = $this->input->get('sort', TRUE);
        switch($list_sort)
        {
            case 'desc': rsort($sort_data);
                break;
            case 'asc': sort($sort_data);
                break;
            default:
                break;
        }
    }
}