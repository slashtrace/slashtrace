<?php

namespace SlashTrace\Context;

use InvalidArgumentException;
use JsonSerializable;

class User implements JsonSerializable
{
    /** @var string */
    private $id;

    /** @var string */
    private $email;

    /** @var string */
    private $name;

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail($email)
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException("Invalid email address");
        }
        $this->email = $email;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    public function jsonSerialize()
    {
        return array_filter([
            "id"    => $this->getId(),
            "email" => $this->getEmail(),
            "name"  => $this->getName(),
        ]);
    }
}