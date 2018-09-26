<?php

namespace SlashTrace\DebugRenderer;

use SlashTrace\Event;

interface DebugRenderer
{
    public function render(Event $event);
}