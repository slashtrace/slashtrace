<?php

namespace SlashTrace\EventHandler;

use Exception;
use SlashTrace\Context\User;

interface EventHandler
{
    const SIGNAL_CONTINUE = 0;
    const SIGNAL_EXIT = 1;

    /**
     * @param Exception $exception
     * @return int
     * @throws EventHandlerException
     */
    public function handleException($exception);

    /**
     * @param string $title
     * @param array $data
     * @return void
     */
    public function recordBreadcrumb($title, array $data = []);

    /**
     * @param User $user
     * @return void
     */
    public function setUser(User $user);

    /**
     * @param string $release
     * @return void
     */
    public function setRelease($release);

    /**
     * @param string $path
     * @return void
     */
    public function setApplicationPath($path);
}