<?php

namespace App;

use Nette\Schema\Elements\Structure;
use Nette\Schema\Expect;
use Nette\Schema\Processor;

class AnimalParser extends ItemParser {
    static protected function schemaDefinition(): Structure
    {
       return Expect::structure([
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
}
