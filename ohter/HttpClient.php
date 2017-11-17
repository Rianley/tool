<?php
/**
 * Htt客户端工具类
 * 基于CURL
 * @author SamDing
 */
namespace koboshi\tool;

class HttpClient
{
    const UA_WIN_SAFARI = 'User-Agent:Mozilla/5.0 (Windows; U; Windows NT 6.1; en-us) AppleWebKit/534.50 (KHTML, like Gecko) Version/5.1 Safari/534.50';

    const UA_MAC_SAFARI = 'User-Agent:Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_8; en-us) AppleWebKit/534.50 (KHTML, like Gecko) Version/5.1 Safari/534.50';

    const UA_WIN_CHROME = 'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_7_0) AppleWebKit/535.11 (KHTML, like Gecko) Chrome/17.0.963.56 Safari/535.11';

    const UA_WIN_IE9 = 'User-Agent:Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; Trident/5.0;';

    const UA_IOS_SAFARI = 'User-Agent:Mozilla/5.0 (iPhone; U; CPU iPhone OS 4_3_3 like Mac OS X; en-us) AppleWebKit/533.17.9 (KHTML, like Gecko) Version/5.0.2 Mobile/8J2 Safari/6533.18.5';

    const UA_ANDROID_QQ = 'User-Agent: MQQBrowser/26 Mozilla/5.0 (Linux; U; Android 2.3.7; zh-cn; MB200 Build/GRJ22; CyanogenMod-7) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/533.1';

    private $config = array();

    public $lastErrno;

    public $lastHttpCode;

    public function __construct($url = null)
    {
        if (!empty($url)) {
            $this->url($url);
        }
    }

    /**
     * @param $url
     * @return $this
     */
    public function url($url)
    {
        $this->config['url'] = trim($url);
        return $this;
    }

    private function processUrl($ch)
    {
        curl_setopt($ch, CURLOPT_URL, $this->config['url']);
        return $ch;
    }

    /**
     * @param $username
     * @param $password
     * @return $this
     */
    public function auth($username, $password)
    {
        $tmp = array();
        $tmp['username'] = $username;
        $tmp['password'] = $password;
        $this->config['auth'] = $tmp;
        return $this;
    }

    private function processAuth($ch)
    {
        $username = $this->config['auth']['username'];
        $password = $this->config['auth']['password'];
        curl_setopt($ch, CURLOPT_USERPWD, "{$username}:{$password}");
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
    }

    /**
     * @param $ua
     * @return $this
     */
    public function ua($ua)
    {
        $this->config['headers']['User-Agent'] = $ua;
        return $this;
    }

    /**
     * @param array $headers
     * @return $this
     */
    public function headers(array $headers)
    {
        $this->config['headers'] = $headers;
        return $this;
    }

    private function processHeaders($ch)
    {
        $headers = array();
        foreach ($this->config['headers'] as $k => $v) {
            $headers[] = "$k:{$v}";
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    }

    /**
     * @param $host
     * @param $port
     * @param null $username
     * @param null $password
     * @return $this
     */
    public function proxy($host, $port, $username = null, $password = null)
    {
        $tmp = array();
        $tmp['host'] = $host;
        $tmp['port'] = $port;
        if(!empty($username) && !empty($password)) {
            $tmp['username'] = $username;
            $tmp['password'] = $password;
        }
        $this->config['proxy'] = $tmp;
        return $this;
    }

    private function processProxy($ch)
    {
        $proxy = $this->config['proxy'];
        curl_setopt($ch, CURLOPT_PROXY, $proxy['host']);
        curl_setopt($ch, CURLOPT_PROXYPORT, $proxy['port']);
        if (!empty($proxy['username']) && !empty($proxy['password'])) {
            curl_setopt($ch, CURLOPT_PROXYAUTH, CURLAUTH_ANY);
            curl_setopt($ch, CURLOPT_PROXYUSERPWD, $proxy['username'] . ':' . $proxy['password']);
        }
    }

    /**
     * @param array $cookies
     * @return $this
     */
    public function cookies(array $cookies)
    {
        $this->config['cookies'] = $cookies;
        return $this;
    }

    private function processCookies($ch)
    {
        $tmp = array();
        foreach ($this->config['cookies'] as $k => $v) {
            $tmp[] = "{$k}={$v}";
        }
        curl_setopt($ch, CURLOPT_COOKIE, implode(';', $tmp));
    }

    /**
     * @param $ms
     * @param null $connectionMs
     * @return $this
     */
    public function timeout($ms, $connectionMs = null)
    {
        $tmp = array();
        $tmp['timeout'] = $ms;
        $tmp['connection_timeout'] = $connectionMs;
        $this->config['timeout'] = $tmp;
        return $this;
    }

    private function processTimeout($ch)
    {
        $timeout = $this->config['timeout'];
        curl_setopt($ch, CURLOPT_TIMEOUT_MS, $timeout['timeout']);
        if (!empty($timeout['connection_timeout'])) {
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, $timeout['connection_timeout']);
        }
    }

    /**
     * @param $postData
     * @return $this
     */
    public function post($postData)
    {
        $tmp = null;
        if (is_array($postData)) {
            $tmp = http_build_query($postData);
        }else {
            $tmp = $postData;
        }
        $this->config['post_data'] = $tmp;
        return $this;
    }

    private function processPost($ch)
    {
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $this->config['post_data']);
    }

    /**
     * @param $eth
     * @return $this
     */
    public function eth($eth)
    {
        $this->config['eth'] = $eth;
        return $this;
    }

    private function processEth($ch)
    {
        curl_setopt($ch, CURLOPT_INTERFACE, $this->config['eth']);
    }

    /**
     * @param null $url
     * @param null $timeout
     * @return mixed
     * @throws \Exception
     */
    public function request($url = null, $timeout = null)
    {
        if (!empty($url)) {
            $this->url($url);
        }
        if (!empty($timeout)) {
            $this->timeout($timeout);
        }
        if (empty($this->config)) {
            throw new \Exception('empty config');
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);//https请求不验证证书
        foreach ($this->config as $k => $v) {
            if (empty($v)) {
                throw new \Exception('empty config: ' . $v);
            }
            $methodName = 'process'  . ucwords($k);
            $this->$methodName($ch);
        }
        $content = curl_exec($ch);
        $this->lastErrno = curl_errno($ch);
        $this->lastHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        $this->reset();
        return $content;
    }

    private function reset()
    {
        $this->config = array();
    }
}