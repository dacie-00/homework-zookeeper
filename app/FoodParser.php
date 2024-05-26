<?php

namespace App;

use Nette\Schema\Elements\Structure;
use Nette\Schema\Expect;

class FoodParser extends ItemParser
{
    static protected function schemaDefinition(): Structure
    {
        return Expect::structure([
            "name" => Expect::string(),
            "namePlural" => Expect::string(),
            "price" => Expect::int(100),
            "nutritionalRatio" => Expect::type("int|float")->default(1),
        ]);
    }
}