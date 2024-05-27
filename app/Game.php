<?php
declare(strict_types=1);

namespace App;


use App\Scenes\AnimalShop;
use App\Scenes\FoodShop;
use App\Scenes\FoodStorage;
use App\Scenes\Zoo;
use stdClass;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;

class Game
{
    public const STATE_ANIMAL_SHOP = "animal shop";
    public const STATE_FOOD_SHOP = "food shop";
    public const STATE_ZOO = "zoo";
    const STATE_ANIMAL_MENU = "animal menu";
    const STATE_FOOD_STORAGE = "food storage";
    const BASE_ANIMAL_PAY = 2;
    static private QuestionHelper $consoleHelper;
    private array $animalTypes;
    /**
     * @var Animal[]
     */
    private array $animals = [];
    private InputInterface $consoleInput;
    private OutputInterface $consoleOutput;
    private string $state;
    private int $money = 1000;

    /**
     * @var Food[]
     */
    private array $foodTypes;
    private array $foods = [];

    public function __construct(InputInterface $consoleInput, OutputInterface $consoleOutput, array $animalTypes, array $foodTypes)
    {
        $this->consoleOutput = $consoleOutput;
        $this->consoleInput = $consoleInput;
        self::$consoleHelper = new QuestionHelper();
        $this->state = self::STATE_ANIMAL_SHOP;
        $this->animalTypes = $animalTypes;
        foreach ($foodTypes as $foodType) {
            $this->foodTypes[$foodType->name] = new Food($foodType);
        }
    }

    public function run()
    {
        while (true) {
            if ($this->state == self::STATE_ANIMAL_SHOP) {
                $animalShop = new AnimalShop($this);
                $animalShop->run();
            }
            if ($this->state == self::STATE_FOOD_SHOP) {
                $foodShop = new FoodShop($this);
                $foodShop->run();
            }
            if ($this->state == self::STATE_FOOD_STORAGE) {
                $foodStorage = new FoodStorage($this);
                $foodStorage->run();
            }
            if ($this->state == self::STATE_ZOO) {
                $zoo = new Zoo($this);
                $zoo->run();
            }
        }
    }

    public function addAnimal(stdClass $properties): Animal
    {
        $animal = new Animal($properties);
        $this->animals[] = $animal;
        return $animal;
    }

    public function animals(): array
    {
        return $this->animals;
    }

    public function step()
    {
        foreach ($this->animals as $index => $animal) {
            $happinessRatio = max(0.25, $animal->happiness() / Animal::HAPPINESS_MAX);
            $action = $animal->actionName();
            $pay = self::BASE_ANIMAL_PAY * $animal->visitorAmusementRatio() * $happinessRatio;
            if ($action == "working") {
                $this->incrementMoney((int) ($pay * 2));
            } elseif ($action == "idling") {
                $this->incrementMoney((int) ($pay * 0.25));
            } else {
                $this->incrementMoney((int) $pay);
            }
            $animal->step();
            if ($animal->dead()) {
                echo "Oh no! {$animal->name()} the {$animal->kind()} has died!\n";
                unset($this->animals[$index]);
            }
        }
    }

    public function incrementMoney($amount): void
    {
        $this->setMoney($this->money() + $amount);
    }

    public function setMoney(int $amount): void
    {
        $this->money = $amount;
    }

    public function money(): int
    {
        return $this->money;
    }

    public function findAnimalByName(string $name): ?Animal
    {
        foreach ($this->animals as $animal) {
            if ($animal->name() == $name) {
                return $animal;
            }
        }
        return null;
    }

    public function findFoodByName(string $name): ?Food
    {
        foreach ($this->foodTypes as $foodType) {
            if ($foodType->name() == $name) {
                return $foodType;
            }
        }
        return null;
    }

    public function consoleInput(): InputInterface
    {
        return $this->consoleInput;
    }

    public function consoleOutput(): OutputInterface
    {
        return $this->consoleOutput;
    }

    public function askChoiceQuestion(string $prompt, array $choices)
    {
        $question = new ChoiceQuestion($prompt, $choices);
        return self::$consoleHelper->ask($this->consoleInput, $this->consoleOutput, $question);
    }

    public function askQuestion(string $prompt, ?callable $validator = null): string
    {
        $question = new Question($prompt);
        if ($validator !== null) {
            $question->setValidator($validator);
        }
        return self::$consoleHelper->ask($this->consoleInput, $this->consoleOutput, $question);
    }

    public function setState(string $state): void
    {
        $this->state = $state;
    }

    public function animalTypes(): array
    {
        return $this->animalTypes;
    }

    public function &foods(): array
    {
        return $this->foods;
    }

    public function decrementMoney($amount): void
    {
        $this->setMoney($this->money() - $amount);
    }

    public function foodTypes()
    {
        return $this->foodTypes;
    }

    public function addFood(Food $food, int $quantity): void
    {
        if (!isset($this->foods[$food->name()])) {
            $this->foods[$food->name()] = $quantity;
            return;
        }
        $this->foods[$food->name()] += $quantity;
    }

    public function consumeFood(Food $food, int $quantity): void
    {
        $this->foods()[$food->name()] -= $quantity;
        if ($this->foods[$food->name()] < 0) {
            unset($this->foods[$food->name()]);
        }
    }
}