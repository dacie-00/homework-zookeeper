<?php

namespace App;

use Nette\Schema\Elements\Structure;
use Nette\Schema\Expect;
use Nette\Schema\Processor;

class AnimalParser
{
    static private Processor $processor;
    static private Structure $schema;

    static public function parse($animal) {
        return self::processor()->process(self::schema(), $animal);
    }

    static private function processor(): Processor
    {
        if (!isset(self::$processor)) {
            self::$processor = new Processor();
        }
        return self::$processor;
    }

    static private function schema(): Structure
    {
        if (!isset(self::$schema)) {
            self::$schema = Expect::structure([
                "kind" => Expect::string(),
                "sound" => Expect::string("undefined"),
                "price" => Expect::int(100),
                "foodReservesDecreaseRate" => Expect::type("int|float")->default(1),
                "foodReservesIncreaseRate" => Expect::type("int|float")->default(1),
                "happinessDecreaseRate" => Expect::type("int|float")->default(1),
                "happinessIncreaseRate" => Expect::type("int|float")->default(1),
                "favoriteFood" => Expect::string("undefined")
            ]);
        }
        return self::$schema;
    }
}