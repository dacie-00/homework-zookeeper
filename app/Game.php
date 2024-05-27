<?php
declare(strict_types=1);

namespace App;


use App\Scenes\AnimalShop;
use App\Scenes\Zoo;
use App\UI\StatBar;
use Closure;
use stdClass;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Helper\Helper;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Helper\Table;
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
    private array $animalTypes;
    /**
     * @var Animal[]
     */
    private array $animals = [];
    private InputInterface $consoleInput;
    private OutputInterface $consoleOutput;
    static private QuestionHelper $consoleHelper;
    private string $state;
    private int $money = 1000;

    private array $foodTypes;
    /**
     * @var Food[]
     */
    private array $foods;

    public function __construct(InputInterface $consoleInput, OutputInterface $consoleOutput, array $animalTypes, array $foodTypes)
    {
        $this->consoleOutput = $consoleOutput;
        $this->consoleInput = $consoleInput;
        self::$consoleHelper = new QuestionHelper();
        $this->state = self::STATE_ANIMAL_SHOP;
        $this->animalTypes = $animalTypes;
        $this->foodTypes = $foodTypes;
        foreach ($foodTypes as $foodType) {
            $this->foods[$foodType->name] = new Food($foodType);
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
                $this->foodShop();
            }
            if ($this->state == self::STATE_ZOO) {
                $zoo = new Zoo($this);
                $zoo->run();
            }
        }
    }

    private function foodShop()
    {
        $this->displayShopFoodsTable();
        while (true) {
            $helper = new QuestionHelper();
            $question = new ChoiceQuestion("What do you want to do?", ["display available foods", "purchase food", "exit shop"]);
            $answer = $helper->ask($this->consoleInput, $this->consoleOutput, $question);
            if ($answer == "exit shop") {
                $this->state = self::STATE_ZOO;
                return;
            }
            if ($answer == "purchase food") {
                $animalNames = array_column($this->foodTypes, "name");
                $question = new ChoiceQuestion("Which food?", $animalNames);
                $answer = $helper->ask($this->consoleInput, $this->consoleOutput, $question);

                $newAnimal = $this->addAnimal($this->animalTypes[$answer]);
                $question = new Question("What will the {$newAnimal->kind()}'s name be? ");
                $name = $helper->ask($this->consoleInput, $this->consoleOutput, $question);
                $newAnimal->setName($name);
                echo "The new animal has been added to your zoo!\n";
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
            if ($animal->dead()) {
                echo "Oh no! {$animal->name()} the {$animal->kind()} has died!\n";
                unset($this->animals[$index]);
            }
            $animal->step();
        }
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
        foreach ($this->foods as $food) {
            if ($food->name() == $name) {
                return $food;
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

    public function foods(): array
    {
        return $this->foods;
    }

    public function money(): int
    {
        return $this->money;
    }

    public function setMoney(int $amount): void
    {
        $this->money = $amount;
    }

    public function incrementMoney($amount): void
    {
        $this->setMoney($this->money() + $amount);
    }

    public function decrementMoney($amount): void
    {
        $this->setMoney($this->money() - $amount);
    }
}