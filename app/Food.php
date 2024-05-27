<?php

namespace App;

use Nette\Schema\Elements\Structure;
use Nette\Schema\Expect;

class Food
{
    private string $name;
    private int $price;
    private float $nutritionalRatio;

    public function __construct(\stdClass $properties)
    {
        $this->name = $properties->name;
        $this->price = $properties->price;
        $this->nutritionalRatio = $properties->nutritionalRatio;
    }

    public static function schema(): Structure
    {
        return Expect::structure([
            "name" => Expect::string(),
            "price" => Expect::int(100),
            "nutritionalRatio" => Expect::type("int|float")->default(1),
        ]);
    }

    public function name(): string
    {
        return $this->name;
    }

    public function nutritionalRatio(): string
    {
        return $this->nutritionalRatio;
    }

    public function price(): string
    {
        return $this->price;
    }
}