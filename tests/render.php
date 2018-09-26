<?php

namespace SlashTrace\Tests\Fixtures;

use SlashTrace\EventHandler\DebugHandler;

require_once __DIR__ . "/bootstrap.php";

$eventProvider = new EventProvider();
$event = $eventProvider->createEvent();
$handler = new DebugHandler();

$handler->handleEvent($event);
