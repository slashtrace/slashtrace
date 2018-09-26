<?php

namespace SlashTrace\Tests\Doubles\Context;

use SlashTrace\Http\Request;

class MockRequest extends Request
{
    private $isXhr = false;

    /** @var array */
    private $get = [];

    /** @var array */
    private $post = [];

    /** @var array */
    private $cookies = [];

    /** @var string */
    private $url;

    /** @var string */
    private $ip;

    public function isXhr()
    {
        return $this->isXhr;
    }

    /**
     * @param bool $value
     */
    public function setIsXhr($value)
    {
        $this->isXhr = $value;
    }

    public function getGetData()
    {
        return $this->get;
    }

    public function getPostData()
    {
        return $this->post;
    }

    public function getCookies()
    {
        return $this->cookies;
    }

    public function setGetData(array $data)
    {
        $this->get = $data;
    }

    public function setPostData(array $data)
    {
        $this->post = $data;
    }

    public function setCookies(array $data)
    {
        $this->cookies = $data;
    }

    public function setHeaders(array $headers)
    {
        $this->headers = $headers;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function setURL($url)
    {
        $this->url = $url;
    }

    public function getIP()
    {
        return $this->ip;
    }

    public function setIP($ip)
    {
        $this->ip = $ip;
    }
}