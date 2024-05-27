<?php
declare(strict_types=1);

namespace App;

use App;
use App\AnimalAction;
use Closure;
use Nette\Schema\Elements\Structure;
use Nette\Schema\Expect;

class Animal
{
    const ACTION_IDLE = "idle";
    const ACTION_PLAY = "play";
    const ACTION_PET = "pet";
    const ACTION_EAT = "eat";
    const ACTION_WORK = "work";
    const FOOD_RESERVES_MAX = 1000;
    const FOOD_RESERVES_MIN = 0;
    const HAPPINESS_MAX = 1000;
    const HAPPINESS_MIN = 0;

    private string $species;
    private string $name;
    private string $favoriteFood;
    private int $happiness;
    private int $foodReserves;
    /**
     * @var callable
     */
    private $defaultAction;
    private AnimalAction $action;
    private string $sound;
    private int $price;
    private float $foodReservesIncreaseRate;
    private float $foodReservesDecreaseRate;
    private float $happinessIncreaseRate;
    private float $happinessDecreaseRate;
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
        $this->favoriteFood = $properties->favoriteFood;
        $this->happiness = 750;
        $this->foodReserves = 750;
        $this->action = new AnimalAction([$this, "idle"], 99999999, ["name" => "idling"]);
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

    public function idle(): void
    {
        $this->decrementFoodReserves(10);
    }

    public function play(): void
    {
        $this->decrementFoodReserves(20);
        $this->incrementHappiness(20);
    }

    public function pet(): void
    {
        $this->decrementFoodReserves(10);
        $this->incrementHappiness(20);
    }

    public function eat(array $data): void
    {
        if ($data["food"] == $this->favoriteFood) {
            $this->incrementFoodReserves(10);
            $this->incrementHappiness(10);
            return;
        }
        $this->decrementFoodReserves(20);
        $this->decrementHappiness(10);
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

    public function foodReserves(): int
    {
        return $this->foodReserves;
    }

    public function happiness(): int
    {
        return $this->happiness;
    }

    public function actionName(): string
    {
        return $this->action->getData()["name"];
    }

    public function incrementFoodReserves(int $amount): void
    {
//        $this->foodReserves += (int) ($amount * $this->foodReservesIncreaseRate);
        $this->setFoodReserves($this->getFoodReserves() + $amount * $this->foodReservesIncreaseRate);
    }

    public function decrementFoodReserves(int $amount): void
    {
//        $this->foodReserves -= (int) ($amount * $this->foodReservesDecreaseRate);
        $this->setFoodReserves($this->getFoodReserves() - $amount * $this->foodReservesDecreaseRate);
    }

    public function incrementHappiness(int $amount): void
    {
//        $this->happiness += (int) ($amount * $this->happinessIncreaseRate);
        $this->setHappiness($this->getHappiness() + $amount * $this->happinessIncreaseRate);
    }

    public function decrementHappiness(int $amount): void
    {
        $this->happiness -= (int) ($amount * $this->happinessDecreaseRate);
        $this->setHappiness($this->getHappiness() - $amount * $this->happinessDecreaseRate);
    }

    public function getHappiness(): int
    {
        return $this->happiness;
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

    public function getFoodReserves(): int
    {
        return $this->foodReserves;
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

    public function setName($name)
    {
        $this->name = $name;
    }

    public function name(): string
    {
        return $this->name;
    }

    public static function schema(): Structure
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

    private function die()
    {
        $this->dead = true;
    }

    public function dead()
    {
        return $this->dead;
    }

    public function price(): int
    {
        return $this->price;
    }
}