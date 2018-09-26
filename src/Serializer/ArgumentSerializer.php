<?php

namespace SlashTrace\Serializer;

use SlashTrace\Formatter\TextVarDumper;
use SlashTrace\Formatter\VarDumper;

class ArgumentSerializer
{
    /** @var HumanSerializer */
    private $humanSerializer;

    /** @var VarDumper */
    private $dumper;

    /**
     * @param mixed $argument
     * @return array
     */
    public function serialize($argument)
    {
        return [
            "type" => gettype($argument),
            "repr" => $this->getHumanSerializer()->serialize($argument),
            "dump" => $this->getDumper()->dump($argument)
        ];
    }

    /**
     * @return HumanSerializer
     */
    public function getHumanSerializer()
    {
        if (is_null($this->humanSerializer)) {
            $this->humanSerializer = new HumanSerializer();
        }
        return $this->humanSerializer;
    }

    public function setHumanSerializer(HumanSerializer $serializer)
    {
        $this->humanSerializer = $serializer;
    }

    /**
     * @return VarDumper
     */
    public function getDumper()
    {
        if (is_null($this->dumper)) {
            $this->dumper = new TextVarDumper();
        }
        return $this->dumper;
    }

    public function setDumper(VarDumper $dumper)
    {
        $this->dumper = $dumper;
    }
}