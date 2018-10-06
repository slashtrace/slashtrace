<?php

namespace SlashTrace\Http;

use JsonSerializable;

class Request implements JsonSerializable
{
    protected $headers = [];

    /**
     * @return string[]
     */
    public function getHeaders()
    {
        if (empty($this->headers)) {
            $this->headers = $this->fetchHeaders();
        }
        return $this->headers;
    }

    public function isXhr()
    {
        return $this->getHeader("X-Requested-With") == "XMLHttpRequest";
    }

    private function fetchHeaders()
    {
        if (function_exists("getallheaders")) {
            return getallheaders();
        }

        $headers = [];

        foreach ($_SERVER as $key => $value) {
            $header = $this->getHeaderName($key);
            if (is_null($header)) {
                continue;
            }
            $headers[$header] = $value;
        }

        return $headers;
    }

    /**
     * @param string $serverKey
     * @return string
     */
    public function getHeaderName($serverKey)
    {
        if (substr($serverKey, 0, 5) == "HTTP_") {
            $serverKey = substr($serverKey, 5);

        } elseif (!in_array($serverKey, ["CONTENT_TYPE", "CONTENT_LENGTH", "CONTENT_MD5"])) {
            return null;
        }

        $header = strtolower($serverKey);

        $parts = explode("_", $header);
        $parts = array_map("ucwords", $parts);

        return implode("-", $parts);
    }

    public function getHeader($header)
    {
        $headers = $this->getHeaders();
        return isset($headers[$header]) ? $headers[$header] : null;
    }

    /**
     * @return array
     */
    public function getGetData()
    {
        return isset($_GET) ? $_GET : [];
    }

    /**
     * @return array
     */
    public function getPostData()
    {
        return isset($_POST) ? $_POST : [];
    }

    /**
     * @return array
     */
    public function getCookies()
    {
        return isset($_COOKIE) ? $_COOKIE : [];
    }

    /**
     * Returns the full URL being requested
     * @return string
     */
    public function getUrl()
    {
        $https = $this->isHTTPS();

        $protocol = strtolower($_SERVER["SERVER_PROTOCOL"]);
        $protocol = substr($protocol, 0, strpos($protocol, "/")) . ($https ? "s" : "");

        $port = $_SERVER["SERVER_PORT"];
        $port = ($port == 80 && !$https) || ($port == 443 && $https) ? "" : ":$port";

        $host = isset($_SERVER["HTTP_HOST"]) ? $_SERVER["HTTP_HOST"] : null;
        $host = $host ? $host : $_SERVER["SERVER_NAME"] . $port;

        return "$protocol://$host" . $_SERVER["REQUEST_URI"];
    }

    private function isHTTPS()
    {
        if (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") {
            return true;
        }
        if ($this->getHeader("X-Forwarded-Proto") == "https") {
            return true;
        }
        return false;
    }

    public function getIP()
    {
        foreach (["HTTP_CLIENT_IP", "HTTP_X_FORWARDED_FOR", "HTTP_X_FORWARDED", "HTTP_X_CLUSTER_CLIENT_IP", "HTTP_FORWARDED_FOR", "HTTP_FORWARDED", "REMOTE_ADDR"] as $key) {
            if (!array_key_exists($key, $_SERVER)) {
                continue;
            }
            foreach (explode(",", $_SERVER[$key]) as $ip) {
                $ip = trim($ip);
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                    return $ip;
                }
            }
        }
        return null;
    }

    public function jsonSerialize()
    {
        return [
            "url"     => $this->getUrl(),
            "headers" => $this->getHeaders(),
            "get"     => $this->getGetData(),
            "post"    => $this->getPostData(),
            "cookies" => $this->getCookies(),
            "ip"      => $this->getIP(),
        ];
    }
}