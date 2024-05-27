<?php
declare(strict_types=1);

namespace App;

use Nette\Schema\Elements\Structure;
use Nette\Schema\Expect;

class Animal
{
    const FOOD_RESERVES_MAX = 1000;
    const FOOD_RESERVES_MIN = 0;
    const HAPPINESS_MAX = 1000;
    const HAPPINESS_MIN = 0;

    private string $name;
    private string $favoriteFood;
    private int $happiness;
    private int $foodReserves;
    /**
     * @var callable
     */
    private AnimalAction $action;
    private string $sound;
    private int $price;
    private float $foodReservesIncreaseRate;
    private float $foodReservesDecreaseRate;
    private float $happinessIncreaseRate;
    private float $happinessDecreaseRate;
    private float $visitorAmusementRatio;
    private bool $dead = false;

    public function __construct($properties)
    {
        $this->kind = $properties->kind;
        $this->sound = $properties->sound;
        $this->price = $properties->price;
        $this->foodReservesIncreaseRate = $properties->foodReservesIncreaseRate;
        $this->foodReservesDecreaseRate = $properties->foodReservesDecreaseRate;
        $this->happinessIncreaseRate = $properties->happinessIncreaseRate;
        $this->happinessDecreaseRate = $properties->happinessDecreaseRate;
        $this->visitorAmusementRatio = $properties->visitorAmusementRatio;
        $this->favoriteFood = $properties->favoriteFood;
        $this->happiness = 750;
        $this->foodReserves = 750;
        $this->action = new AnimalAction([$this, "idle"], 99999999, ["name" => "idling"]);
    }

    public static function schema(): Structure
    {
        return Expect::structure([
            "kind" => Expect::string()->required(),
            "sound" => Expect::string()->required(),
            "price" => Expect::int()->required(),
            "foodReservesDecreaseRate" => Expect::type("int|float")->required(),
            "foodReservesIncreaseRate" => Expect::type("int|float")->required(),
            "happinessDecreaseRate" => Expect::type("int|float")->required(),
            "happinessIncreaseRate" => Expect::type("int|float")->required(),
            "visitorAmusementRatio" => Expect::type("int|float")->required(),
            "favoriteFood" => Expect::string("")->required(),
        ]);
    }

    public function setAction(callable $action, int $times = 1, array $data = []): void
    {
        $this->action = new AnimalAction($action, $times, $data);
    }

    public function step(): void
    {
        if ($this->foodReserves == 0) {
            $this->die();
        }
        if (!$this->action->perform()) {
            // in practice "times" here is INF, but we can't put in INF because it isn't an integer
            $this->action = new AnimalAction([$this, "idle"], 999999999, ["name" => "idling"]);
            $this->action->perform();
        }
    }

    private function die(): void
    {
        $this->dead = true;
    }

    public function idle(): void
    {
        $this->decrementHappiness(3);
        $this->decrementFoodReserves(10);
    }

    public function decrementHappiness(int $amount): void
    {
        $this->setHappiness((int)($this->happiness() - $amount * $this->happinessDecreaseRate));
    }

    public function setHappiness(int $happiness): void
    {
        $this->happiness = $happiness;
        if ($this->happiness < self::HAPPINESS_MIN) {
            $this->happiness = self::HAPPINESS_MIN;
            return;
        }
        if ($this->happiness > self::HAPPINESS_MAX) {
            $this->happiness = self::HAPPINESS_MAX;
        }
    }

    public function happiness(): int
    {
        return $this->happiness;
    }

    public function decrementFoodReserves(int $amount): void
    {
        $this->setFoodReserves((int)($this->foodReserves() - $amount * $this->foodReservesDecreaseRate));
    }

    public function setFoodReserves(int $foodReserves): void
    {
        $this->foodReserves = $foodReserves;
        if ($this->foodReserves < self::FOOD_RESERVES_MIN) {
            $this->foodReserves = self::FOOD_RESERVES_MIN;
            return;
        }
        if ($this->foodReserves > self::FOOD_RESERVES_MAX) {
            $this->foodReserves = self::FOOD_RESERVES_MAX;
        }
    }

    public function foodReserves(): int
    {
        return $this->foodReserves;
    }

    public function play(): void
    {
        $this->decrementFoodReserves(20);
        $this->incrementHappiness(20);
    }

    public function incrementHappiness(int $amount): void
    {
        $this->setHappiness((int)($this->happiness() + $amount * $this->happinessIncreaseRate));
    }

    public function pet(): void
    {
        $this->decrementFoodReserves(10);
        $this->incrementHappiness(20);
    }

    public function eat(array $data): void
    {
        if ($data["food"]->name() == $this->favoriteFood) {
            $this->incrementFoodReserves((int)(10 * $data["food"]->nutritionalRatio()));
            $this->incrementHappiness(10);
            return;
        }
        $this->decrementFoodReserves(20);
        $this->decrementHappiness(10);
    }

    public function name(): string
    {
        return $this->name;
    }

    public function incrementFoodReserves(int $amount): void
    {
        $this->setFoodReserves((int)($this->foodReserves() + $amount * $this->foodReservesIncreaseRate));
    }

    public function work(): void
    {
        $this->decrementFoodReserves(15);
        $this->decrementHappiness(15);
    }

    public function kind(): string
    {
        return $this->kind;
    }

    public function action(): AnimalAction
    {
        return $this->action;
    }

    public function actionName(): string
    {
        return $this->action->getData()["name"];
    }

    public function setName($name): void
    {
        $this->name = $name;
    }

    public function dead(): bool
    {
        return $this->dead;
    }

    public function price(): int
    {
        return $this->price;
    }

    public function visitorAmusementRatio(): float
    {
        return $this->visitorAmusementRatio;
    }

    public function sound(): string
    {
        return $this->sound;
    }
}