<?php

namespace SlashTrace\Serializer;

use DateTime;

class Serializer
{
    /** @var int */
    private $maxLength = 1000;

    /** @var int */
    private $maxDepth = 3;

    /** @var string */
    private $ellipsis = "[...]";

    public function serialize($input, $depth = 0)
    {
        if ($this->isPrimitive($input)) {
            return $input;
        }
        if ($this->isIterable($input) && $depth < $this->maxDepth) {
            return $this->serializeIterable($input, $depth);
        }
        return $this->toString($input);
    }

    private function serializeIterable($input, $depth = 0)
    {
        $return = [];
        foreach ($input as $key => $value) {
            $return[$this->serialize($key)] = $this->serialize($value, $depth + 1);
        }
        return $return;
    }

    public function toString($input, $maxLength = null)
    {
        if (is_array($input)) {
            return "array[" . count($input) . "]";

        } elseif (is_object($input)) {
            if ($input instanceof DateTime) {
                return "DateTime[" . $this->serializeDate($input) . "]";
            }
            return "Object[" . get_class($input) . "]";

        } else if (is_resource($input)) {
            return "resource[" . get_resource_type($input) . "]";
        }
        return $this->serializeString($input, $maxLength);
    }

    private function serializeString($input, $maxLength = null)
    {
        if (is_null($maxLength)) {
            $maxLength = $this->getMaxLength();
        }

        $input = strval($input);
        $length = $this->strlen($input);
        if ($length > $maxLength) {
            return $this->substr($input, 0, $maxLength) . $this->ellipsis;
        }

        return $input;
    }

    public function serializeDate(DateTime $input)
    {
        return $input->format(DATE_ATOM);
    }

    private function isPrimitive($input)
    {
        return is_null($input) || is_bool($input) || is_float($input) || is_integer($input);
    }

    private function isIterable($input)
    {
        return is_array($input) || (is_object($input) && get_class($input) == "stdClass");
    }

    /**
     * @return int
     */
    public function getMaxLength()
    {
        return $this->maxLength;
    }

    private function strlen($input)
    {
        return function_exists("mb_strlen") ? mb_strlen($input) : strlen($input);
    }

    private function substr($input, $start, $length = null)
    {
        return function_exists("mb_substr") ? mb_substr($input, $start, $length) : substr($input, $start, $length);
    }
}