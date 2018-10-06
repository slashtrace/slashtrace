<?php

namespace SlashTrace\Context;

use Exception;
use JsonSerializable;
use SlashTrace\Http\Request;

class EventContext implements JsonSerializable
{
    /** @var string */
    private $release;

    /** @var Request */
    private $httpRequest;

    /** @var array */
    private $server = [];

    /** @var User */
    private $user;

    /** @var Breadcrumbs */
    private $breadcrumbs;

    /** @var string */
    private $applicationPath;

    /**
     * @return string
     */
    public function getRelease()
    {
        return $this->release;
    }

    /**
     * @param string $release
     */
    public function setRelease($release)
    {
        $this->release = $release;
    }

    /**
     * @return Request
     */
    public function getHTTPRequest()
    {
        return $this->httpRequest;
    }

    /**
     * @param Request $request
     */
    public function setHttpRequest(Request $request)
    {
        $this->httpRequest = $request;
    }

    /**
     * @return array
     */
    public function getServer()
    {
        return $this->server;
    }

    public function setServer(array $server)
    {
        $this->server = $server;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param User $user
     * @throws Exception
     */
    public function setUser(User $user)
    {
        if (is_null($user->getId()) && is_null($user->getEmail())) {
            throw new Exception("User must have ID or email address");
        }
        $this->user = $user;
    }

    /**
     * @return Breadcrumbs
     */
    public function getBreadcrumbs()
    {
        return $this->breadcrumbs;
    }

    public function setBreadcrumbs(Breadcrumbs $breadcrumbs)
    {
        $this->breadcrumbs = $breadcrumbs;
    }

    /**
     * @return string
     */
    public function getApplicationPath()
    {
        return $this->applicationPath;
    }

    /**
     * @param string $applicationPath
     */
    public function setApplicationPath($applicationPath)
    {
        // Force the use of the cross-platform directory separator
        $this->applicationPath = str_replace("\\", "/", $applicationPath);
    }

    public function hasCustomData()
    {
        $breadcrumbs = $this->getBreadcrumbs();
        if (!is_null($breadcrumbs) && !$breadcrumbs->isEmpty()) {
            return true;
        }
        return !is_null($this->getRelease()) || !is_null($this->getUser());
    }

    public function jsonSerialize()
    {
        return array_filter([
            "request"          => $this->getHTTPRequest(),
            "server"           => $this->getServer(),
            "user"             => $this->getUser(),
            "breadcrumbs"      => $this->getBreadcrumbs(),
            "release"          => $this->getRelease(),
            "application_path" => $this->getApplicationPath(),
        ]);
    }
}