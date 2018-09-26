<?php

namespace SlashTrace\Serializer;

class HumanSerializer
{
    /** @var Serializer */
    private $serializer;

    /**
     * @param mixed $input
     * @param int $maxLength
     * @return string
     */
    public function serialize($input, $maxLength = 100)
    {
        if (is_null($input)) {
            return "null";
        }
        if ($input === true) {
            return "true";
        }
        if ($input === false) {
            return "false";
        }
        if (is_float($input)) {
            if (intval($input) == $input) {
                return "$input.0";
            }
            return strval($input);
        }
        if (is_integer($input)) {
            return strval($input);
        }

        return $this->toString($input, $maxLength);
    }

    private function toString($input, $maxLength)
    {
        $toString = $this->getSerializer()->toString($input, $maxLength);
        if (is_string($input) && !is_callable($input)) {
            return '"' . $toString . '"';
        }
        return $toString;
    }

    /**
     * @return Serializer
     */
    private function getSerializer()
    {
        if (is_null($this->serializer)) {
            $this->serializer = new Serializer();
        }
        return $this->serializer;
    }
}