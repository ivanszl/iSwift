<?php
namespace iSwift\Web;

use iSwift;
use iSwift\Base\InvalidParamException;

class Output extends \iSwift\Context\Output
{
    public static $httpStatuses = [
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        118 => 'Connection timed out',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',
        208 => 'Already Reported',
        210 => 'Content Different',
        226 => 'IM Used',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => 'Reserved',
        307 => 'Temporary Redirect',
        308 => 'Permanent Redirect',
        310 => 'Too many Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Time-out',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested range unsatisfiable',
        417 => 'Expectation failed',
        418 => 'I\'m a teapot',
        422 => 'Unprocessable entity',
        423 => 'Locked',
        424 => 'Method failure',
        425 => 'Unordered Collection',
        426 => 'Upgrade Required',
        428 => 'Precondition Required',
        429 => 'Too Many Requests',
        431 => 'Request Header Fields Too Large',
        449 => 'Retry With',
        450 => 'Blocked by Windows Parental Controls',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway or Proxy Error',
        503 => 'Service Unavailable',
        504 => 'Gateway Time-out',
        505 => 'HTTP Version not supported',
        507 => 'Insufficient storage',
        508 => 'Loop Detected',
        509 => 'Bandwidth Limit Exceeded',
        510 => 'Not Extended',
        511 => 'Network Authentication Required',
    ];

    public function __construct()
    {
        $this->injectors = [
                'status'       => 200,
                'body'         => '',
                'content_type' => 'text/html',
                'charset'      => iSwift::$app->charset,
                'headers'      => [],
                'cookies'      => [],
                'isSent'       => false,
                'sendFile'     => null,
                'format'       => 'html'
            ] + $this->injectors;
    }
    public function status($status = null)
    {
        if ($status === null) {
            return $this->injectors['status'];
        } elseif (isset(self::$httpStatuses[$status])) {
            $this->injectors['status'] = (int)$status;
            return $this;
        } else {
            throw new InvalidParamException('Invalid param status :value', [':value' => $status]);
        }
    }

    public function send()
    {
        if ($this->injectors['isSent']) {
            return;
        }
        $this->sendHeaders();
        $this->sendContent();
        $this->injectors['isSent'] = true;
    }

    public function clear()
    {
        $this->injectors = [
                'status'       => 200,
                'body'         => '',
                'content_type' => 'text/html',
                'charset'      => iSwift::$app->charset,
                'headers'      => [],
                'cookies'      => [],
                'isSent'       => false,
                'statusText'   => 'ok',
                'sendFile'     => null,
            ] + $this->injectors;
        unset($this->injectors['sendFile']);
    }

    protected function sendHeaders()
    {
        if (headers_sent()) {
            return;
        }
        if ($this->injectors['headers']) {
            $headers = $this->injectors['headers'];
            foreach ($headers as $name => $value) {
                $name = str_replace(' ', '-', ucwords(str_replace('-', ' ', $name)));
                header("$name: $value");
            }
        }
        $statusCode = $this->injectors['status'];
        $statusText = self::$httpStatuses[$statusCode];
        header("HTTP/1.1 {$statusCode} {$statusText}");
        $this->sendCookies();
    }

    protected function sendCookies()
    {
        if ($this->injectors['cookies'] === null) {
            return;
        }

        foreach ($this->injectors['cookies'] as $name=>$cookie) {
            $value = $cookie['value'] or '';
            setcookie($name, $value, $cookie['expire'], $cookie['path'], $cookie['domain'], $cookie['secure'], $cookie['httpOnly']);
        }
    }

    protected function sendContent()
    {
        if ($this->injectors['sendFile'] === null) {
            echo $this->injectors['body'];
        } else {
            readfile($this->injectors['sendFile']);
        }
    }

    public function download($file, $name = null)
    {
        $attachmentName = $name ? $name : basename($file);
        $this->header('Pragma', 'public')
            ->header('Accept-Ranges', 'bytes')
            ->header('Expires', '0')
            ->header('Cache-Control', 'must-revalidate, post-check=0, pre-check=0')
            ->header('Content-Disposition', "$disposition; filename=\"$attachmentName\"")
            ->header('Content-Transfer-Encoding', 'binary')
            ->header('Content-Length', filesize($file));
        $this->injectors['sendFile'] = $file;
        return $this;
    }

    public function header($key, $value)
    {
        $this->injectors['headers'][$key] = $value;
        return $this;
    }

    public function redirect($url, $statusCode = 302, $checkAjax = true)
    {
        if ($url === null)
        {
            $url = '/';
        }

        if (strpos($url, '/') === 0 && strpos($url, '//') !== 0) {
            $url = iSwift::$app->input->site() . $url;
        }

        if ($checkAjax) {
            if (iSwift::$app->input->isAjax()) {
                if (iSwift::$app->input->header('X-Ie-Redirect-Compatibility') !== null && $statusCode === 302) {
                    // Ajax 302 redirect in IE does not work. Change status code to 200. See https://github.com/yiisoft/yii2/issues/9670
                    $statusCode = 200;
                }

                $this->header('X-Redirect', $url);
            } else {
                $this->header('Location', $url);
            }
        } else {
            $this->header('Location', $url);
        }

        $this->status($statusCode);

        return $this;
    }

    public function cookie($name, $cookie)
    {
        if ($this->injectors['cookies'] === null) {
            $this->injectors['cookies'] = [];
        }
        if (!isset($cookie['expire']))
        {
            $cookie['expire'] = 0;
        }
        if (!isset($cookie['path']))
        {
            $cookie['path'] = '/';
        }
        if (!isset($cookie['secure']))
        {
            $cookie['secure'] = false;
        }
        if (!isset($cookie['httpOnly']))
        {
            $cookie['httpOnly'] = true;
        }
        if (!isset($cookie['domain']))
        {
            $cookie['domain'] = '';
        }
        $this->injectors['cookies'][$name] = $cookie;
    }

    /**
     * Is response cachable?
     *
     * @return bool
     */
    public function isCachable()
    {
        return $this->injectors['status'] >= 200 && $this->injectors['status'] < 300 || $this->injectors['status'] == 304;
    }
    /**
     * Is empty?
     *
     * @return bool
     */
    public function isEmpty()
    {
        return in_array($this->injectors['status'], array(201, 204, 304));
    }
    /**
     * Is 200 ok?
     *
     * @return bool
     */
    public function isOk()
    {
        return $this->injectors['status'] === 200;
    }
    /**
     * Is successful?
     *
     * @return bool
     */
    public function isSuccessful()
    {
        return $this->injectors['status'] >= 200 && $this->injectors['status'] < 300;
    }
    /**
     * Is redirect?
     *
     * @return bool
     */
    public function isRedirect()
    {
        return in_array($this->injectors['status'], array(301, 302, 303, 307));
    }
    /**
     * Is forbidden?
     *
     * @return bool
     */
    public function isForbidden()
    {
        return $this->injectors['status'] === 403;
    }
    /**
     * Is found?
     *
     * @return bool
     */
    public function isNotFound()
    {
        return $this->injectors['status'] === 404;
    }
    /**
     * Is client error?
     *
     * @return bool
     */
    public function isClientError()
    {
        return $this->injectors['status'] >= 400 && $this->injectors['status'] < 500;
    }
    /**
     * Is server error?
     *
     * @return bool
     */
    public function isServerError()
    {
        return $this->injectors['status'] >= 500 && $this->injectors['status'] < 600;
    }

    public function __toString()
    {
        return $this->injectors['body'];
    }
}
