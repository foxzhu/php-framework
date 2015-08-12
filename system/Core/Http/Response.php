<?php

namespace Core\Http;


/**
 * Response 输出类
 *
 * @author lisijie <lsj86@qq.com>
 * @package Core
 */
class Response
{
    //头信息
    protected $headers = null;
    //cookies
    protected $cookies = null;
    //内容
    protected $body = '';
    //状态码
    protected $status = 200;
    //状态文本
    protected $statusText = '';
    //协议
    protected $protocol = 'HTTP/1.1';
    //字符集
    protected $charset;
    //http状态码
    protected static $httpCodes = array(
        //Informational 1xx
        100 => '100 Continue',
        101 => '101 Switching Protocols',
        //Successful 2xx
        200 => '200 OK',
        201 => '201 Created',
        202 => '202 Accepted',
        203 => '203 Non-Authoritative Information',
        204 => '204 No Content',
        205 => '205 Reset Content',
        206 => '206 Partial Content',
        //Redirection 3xx
        300 => '300 Multiple Choices',
        301 => '301 Moved Permanently',
        302 => '302 Found',
        303 => '303 See Other',
        304 => '304 Not Modified',
        305 => '305 Use Proxy',
        306 => '306 (Unused)',
        307 => '307 Temporary Redirect',
        //Client Error 4xx
        400 => '400 Bad Request',
        401 => '401 Unauthorized',
        402 => '402 Payment Required',
        403 => '403 Forbidden',
        404 => '404 Not Found',
        405 => '405 Method Not Allowed',
        406 => '406 Not Acceptable',
        407 => '407 Proxy Authentication Required',
        408 => '408 Request Timeout',
        409 => '409 Conflict',
        410 => '410 Gone',
        411 => '411 Length Required',
        412 => '412 Precondition Failed',
        413 => '413 Request Entity Too Large',
        414 => '414 Request-URI Too Long',
        415 => '415 Unsupported Media Type',
        416 => '416 Requested Range Not Satisfiable',
        417 => '417 Expectation Failed',
        418 => '418 I\'m a teapot',
        422 => '422 Unprocessable Entity',
        423 => '423 Locked',
        //Server Error 5xx
        500 => '500 Internal Server Error',
        501 => '501 Not Implemented',
        502 => '502 Bad Gateway',
        503 => '503 Service Unavailable',
        504 => '504 Gateway Timeout',
        505 => '505 HTTP Version Not Supported'
    );

    public function __construct($content = '')
    {
        $this->body = $content;
    }

    /**
     * 获取用于发送的cookies对象
     *
     * @return Cookies
     */
    public function cookies()
    {
        if (null === $this->cookies) {
            $this->cookies = new Cookies(array());
        }
        return $this->cookies;
    }

    /**
     * 获取用于发送的headers对象
     *
     * @return Headers
     */
    public function headers()
    {
        if (null === $this->headers) {
            $this->headers = new Headers(array());
        }
        return $this->headers;
    }

    /**
     * URL重定向
     *
     * @param string $url
     * @return $this
     */
    public function redirect($url)
    {
        $this->setStatus(302);
        $this->setHeader('Location', $url);
        return $this;
    }

    /**
     * 设置输出http状态
     *
     * @param int $code 状态码
     * @param string $text 文本
     * @return $this
     */
    public function setStatus($code, $text = null)
    {
        $this->status = $code;
        if (null === $text) {
            $text = isset(self::$httpCodes[$code]) ? self::$httpCodes[$code] : '';
        }
        $this->statusText = $text;
        return $this;
    }

    /**
     * 获取HTTP状态码
     *
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * 获取状态码对应信息
     *
     * @param int $code 状态码
     * @return string
     */
    public static function getStatusMessage($code)
    {
        return isset(static::$httpCodes[$code]) ? static::$httpCodes[$code] : '';
    }

    /**
     * 设置输出内容
     *
     * @param string $body
     * @return $this
     */
    public function setBody($body)
    {
        $this->body = $body;
        return $this;
    }

    /**
     * 追加输出内容
     *
     * @param string $body
     * @return $this
     */
    public function appendBody($body)
    {
        $this->body .= $body;
        return $this;
    }

    /**
     * 获取输出内容
     *
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * 返回输出内容长度
     *
     * @return int
     */
    public function getBodyLength()
    {
        return strlen($this->body);
    }

    /**
     * 输出数据
     *
     * @throws \RuntimeException
     */
    public function send()
    {
        if (!headers_sent($filename, $line)) {
            $this->sendHeaders();
            $this->sendCookies();
        } else {
            throw new \RuntimeException("Headers already sent in $filename on line $line");
        }
        $this->sendBody();
    }

    protected function sendHeaders()
    {
        if (!$this->headers) {
            return;
        }
        if ($this->status != 200 && isset(self::$httpCodes[$this->status])) {
            header(sprintf("%s %s", $this->protocol, self::$httpCodes[$this->status]));
        }
        if (!$this->headers->has('content-type')) {
            $this->headers->set('content-type', 'text/html; charset=utf-8');
        }
        foreach ($this->headers as $key => $value) {
            header("{$key}: $value", true);
        }
    }

    protected function sendCookies()
    {
        if (!$this->cookies) {
            return;
        }
        foreach ($this->cookies as $key => $value) {
            $str = $this->cookies->parseValue($key, $value);
            header("Set-Cookie: $str", true);
        }
    }

    protected function sendBody()
    {
        echo $this->body;
    }
}
