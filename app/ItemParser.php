<?php

namespace App;

use Nette\Schema\Elements\Structure;
use Nette\Schema\Processor;

class ItemParser
{
    private static Processor $processor;
    private Structure $schema;

    public function __construct(Structure $schema)
    {
        $this->schema = $schema;
    }

    public function parse($path)
    {
        $animal = json_decode(file_get_contents($path), false, 512, JSON_THROW_ON_ERROR);
        return self::processor()->process($this->schema(), $animal);
    }
    private function processor(): Processor
    {
        if (!isset(self::$processor)) {
            self::$processor = new Processor();
        }
        return self::$processor;
    }

    private function schema(): Structure
    {
        return $this->schema;
    }
}