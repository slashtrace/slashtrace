<?php

namespace SlashTrace\Tests\Fixtures\Code;

use DateTime;
use stdClass;

function do_something(stdClass $person, $a, $b)
{
    assert($a);
    assert(!$b);

    $testClass = new TestClass($person);
    $testClass->doSomething(new DateTime());
}

function create_person()
{
    $person = new stdClass();

    $person->name = "John Doe";
    $person->email = "john.doe@example.com";
    $person->spokenLanguages = ["English", "Swahili"];
    $person->preferences = ["name" => "value"];

    $address = new stdClass();
    $address->country = "Romania";
    $address->city = "Bucharest";

    $person->address = $address;
    $person->date = new DateTime();

    return $person;
}