<?php
namespace iSwift\Web;

class Input extends \iSwift\Context\Input
{
    public function __construct()
    {
        $this->append([
            'query'  => &$_GET,
            'data'   => &$_POST,
            'files'  => &$_FILES,
            'server' => &$_SERVER
        ]);
    }

    public function protocol()
    {
        return $this->injectors['server']['SERVER_PROTOCOL'];
    }

    public function uri()
    {
        return $this->injectors['server']['REQUEST_URI'];
    }

    public function root()
    {
        return rtrim($this->injectors['server']['DOCUMENT_ROOT'], '/') . rtrim($this->scriptName(), '/');
    }

    public function scriptName($basename = null)
    {
        $_script_name = $this->injectors['server']['SCRIPT_NAME'];
        if ($basename === null) {
            if (strpos($this->injectors['server']['REQUEST_URI'], $_script_name) !== 0) {
                $_script_name = dirname($_script_name);
            }
        } else if (!$basename) {
            $_script_name = dirname($_script_name);
        }
        return rtrim(str_replace('\\', '/', $_script_name), '/');
    }

    public function path()
    {
        if (!isset($this->injectors['path_info'])) {
            $_path_info = substr_replace($this->injectors['server']['REQUEST_URI'], '', 0, strlen($this->scriptName()));
            if (strpos($_path_info, '?') !== false) {
                // Query string is not removed automatically
                $_path_info = substr_replace($_path_info, '', strpos($_path_info, '?'));
            }
            $this->injectors['path_info'] = (!$_path_info || $_path_info{0} != '/' ? '/' : '') . $_path_info;
        }
        return $this->injectors['path_info'];
    }
    public function url($full = true)
    {
        if (!$full) return $this->injectors['server']['REQUEST_URI'];
        return $this->site() . $this->injectors['server']['REQUEST_URI'];
    }

    public function site($basename = null)
    {
        return $this->scheme() . '://' . $this->domain() . $this->scriptName($basename);
    }

    public function method()
    {
        return $this->injectors['server']['REQUEST_METHOD'];
    }

    public function ip($proxy = true)
    {
        if ($proxy && ($ips = $this->proxy())) {
            return $ips[0];
        }
        return $this->injectors['server']['REMOTE_ADDR'];
    }

    public function proxy()
    {
        if (empty($this->injectors['server']['HTTP_X_FORWARDED_FOR'])) return array();
        $ips = $this->injectors['server']['HTTP_X_FORWARDED_FOR'];
        return strpos($ips, ', ') ? explode(', ', $ips) : array($ips);
    }

    public function subDomains()
    {
        $parts = explode('.', $this->host());
        return array_reverse(array_slice($parts, 0, -2));
    }


    public function refer()
    {
        return isset($this->injectors['server']['HTTP_REFERER']) ? $this->injectors['server']['HTTP_REFERER'] : null;
    }

    public function host($port = false)
    {
        if (isset($this->injectors['server']['HTTP_HOST']) && ($host = $this->injectors['server']['HTTP_HOST'])) {
            if ($port) return $host;
            if (strpos($host, ':') !== false) {
                $hostParts = explode(':', $host);
                return $hostParts[0];
            }
            return $host;
        }
        return $this->injectors['server']['SERVER_NAME'];
    }

    public function domain()
    {
        return $this->host();
    }

    public function scheme()
    {
        return !isset($this->injectors['server']['HTTPS']) || $this->injectors['server']['HTTPS'] == 'off' ? 'http' : 'https';
    }

    public function port()
    {
        return empty($this->injectors['server']['SERVER_PORT']) ? null : (int)$this->injectors['server']['SERVER_PORT'];
    }

    public function userAgent()
    {
        return !empty($this->injectors['server']['HTTP_USER_AGENT']) ? $this->injectors['server']['HTTP_USER_AGENT'] : null;
    }

    public function type()
    {
        if (empty($this->injectors['server']['CONTENT_TYPE'])) return null;
        $type = $this->injectors['server']['CONTENT_TYPE'];
        $parts = preg_split('/\s*[;,]\s*/', $type);
        return strtolower($parts[0]);
    }

    public function charset()
    {
        if (empty($this->injectors['server']['CONTENT_TYPE'])) return null;
        $type = $this->injectors['server']['CONTENT_TYPE'];
        if (!preg_match('/charset=([a-z0-9\-]+)/', $type, $match)) return null;
        return strtolower($match[1]);
    }

    public function length()
    {
        if (empty($this->injectors['server']['CONTENT_LENGTH'])) return 0;
        return (int)$this->injectors['server']['CONTENT_LENGTH'];
    }

    public function file($key)
    {
        return isset($this->injectors['files'][$key]) ? $this->injectors['files'][$key] : null;
    }

    public function query($key, $default = null)
    {
        if (isset($this->injectors['query'][$key])) {
            return $this->injectors['query'][$key];
        }
        return $default;
    }

    public function data($key, $default = null)
    {
        if (isset($this->injectors['data'][$key])) {
            return $this->injectors['data'][$key];
        }
        return $default;
    }

    public function server($key, $default = null)
    {
        return isset($this->injectors['server'][$key]) ? $this->injectors['server'][$key] : $default;
    }

    public function header($name = null)
    {
        if (!isset($this->injectors['headers'])) {
            $_header = array();
            foreach ($this->injectors['server'] as $key => $value) {
                $_name = false;
                if ('HTTP_' === substr($key, 0, 5)) {
                    $_name = substr($key, 5);
                } elseif ('X_' == substr($key, 0, 2)) {
                    $_name = substr($key, 2);
                } elseif (in_array($key, array('CONTENT_LENGTH',
                    'CONTENT_MD5',
                    'CONTENT_TYPE'))
                ) {
                    $_name = $key;
                }
                if (!$_name) continue;
                // Set header
                $_header[strtolower(str_replace('_', '-', $_name))] = trim($value);
            }
            if (isset($this->injectors['server']['PHP_AUTH_USER'])) {
                $pass = isset($this->injectors['server']['PHP_AUTH_PW']) ? $this->injectors['server']['PHP_AUTH_PW'] : '';
                $_header['authorization'] = 'Basic ' . base64_encode($this->injectors['server']['PHP_AUTH_USER'] . ':' . $pass);
            }
            $this->injectors['headers'] = $_header;
            unset($_header);
        }
        if ($name === null) return $this->injectors['headers'];
        $name = strtolower(str_replace('_', '-', $name));
        return isset($this->injectors['headers'][$name]) ? $this->injectors['headers'][$name] : null;
    }

    public function cookie($key = null, $default = null)
    {
        if (!isset($this->injectors['cookies'])) {
            $this->injectors['cookies'] = $_COOKIE;
        }
        if ($key === null) return $this->injectors['cookies'];
        return isset($this->injectors['cookies'][$key]) ? $this->injectors['cookies'][$key] : $default;
    }

    public function session($key = null, $value = null)
    {
        if ($value !== null) {
            return $_SESSION[$key] = $value;
        } elseif ($key !== null) {
            return isset($_SESSION[$key]) ? $_SESSION[$key] : null;
        }
        return $_SESSION;
    }

    public function body()
    {
        if (!isset($this->injectors['body'])) {
            $this->injectors['body'] = @(string)file_get_contents('php://input');
        }
        return $this->injectors['body'];
    }

    public function is($method)
    {
        return $this->method() == strtoupper($method);
    }

    public function isAjax()
    {
        return $this->header('x-requested-with') && 'XMLHttpRequest' == $this->header('x-requested-with');
    }

    public function isSecure()
    {
        return $this->scheme() === 'https';
    }

    public function isUpload()
    {
        return !empty($this->injectors['files']);
    }
}
