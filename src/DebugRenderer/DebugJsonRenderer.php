<?php

namespace SlashTrace\DebugRenderer;

use SlashTrace\Event;
use SlashTrace\System\HasSystemProvider;

class DebugJsonRenderer implements DebugRenderer
{
    use HasSystemProvider;

    public function render(Event $event)
    {
        $this->getSystem()->output(
            json_encode(array_merge(
                ["success" => false],
                $event->jsonSerialize()
            ))
        );
    }
}