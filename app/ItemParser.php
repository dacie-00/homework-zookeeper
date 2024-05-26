<?php

namespace App;

use Nette\Schema\Elements\Structure;
use Nette\Schema\Processor;

abstract class ItemParser
{
    private static Processor $processor;
    private static Structure $schema;

    static public function parse($path)
    {
        $animal = json_decode(file_get_contents($path), false, 512, JSON_THROW_ON_ERROR);
        return self::processor()->process(self::schema(), $animal);
    }
    static protected function processor(): Processor
    {
        if (!isset(self::$processor)) {
            self::$processor = new Processor();
        }
        return self::$processor;
    }

    static protected function schema(): Structure
    {
        if (!isset(self::$schema)) {
            self::$schema = static::schemaDefinition();
        }
        return self::$schema;
    }

    static protected abstract function schemaDefinition(): Structure;
}