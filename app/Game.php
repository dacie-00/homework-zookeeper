<?php
declare(strict_types=1);

namespace App;


use App\Scenes\AnimalShop;
use App\UI\StatBar;
use stdClass;
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
    private array $animalTypes;
    /**
     * @var Animal[]
     */
    private array $animals = [];
    private InputInterface $consoleInput;
    private OutputInterface $consoleOutput;
    static private QuestionHelper $consoleHelper;
    private string $state;

    private array $foodTypes;

    public function __construct(InputInterface $consoleInput, OutputInterface $consoleOutput, array $animalTypes, array $foodTypes)
    {
        $this->consoleInput = $consoleInput;
        $this->consoleOutput = $consoleOutput;
        self::$consoleHelper = new QuestionHelper();
        $this->state = self::STATE_ANIMAL_SHOP;
        $this->animalTypes = $animalTypes;
        $this->foodTypes = $foodTypes;
    }

    public function displayAnimal(Animal $animal): void
    {
        $actionName = $animal->actionName();
        echo "{$animal->name()} the {$animal->kind()} (currently $actionName)\n";

        $foodReserves = $animal->foodReserves();
        $happiness = $animal->happiness();

        StatBar::display($this->consoleOutput, "Food Reserves", $foodReserves);
        StatBar::display($this->consoleOutput, "Happiness    ", $happiness);
    }

    public function run($input, $output)
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
                $this->zoo();
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

    private function zoo(): void
    {
    }

    public function animals(): array
    {
        return $this->animals;
    }

    private function displayBar(string $name, int $value, int $max, $color = "bright-white"): ProgressBar
    {
        $displayBar = new ProgressBar($this->consoleOutput, $max);
        $displayBar->setFormat("$name [<fg=$color>%bar%</>]\n");
        $displayBar->setProgress($value);
        return $displayBar;
    }

    private function step()
    {
        foreach ($this->animals as $animal) {
            $animal->step();
        }
    }

    private function findAnimalByName(string $name): ?Animal
    {
        foreach ($this->animals as $animal) {
            if ($animal->name() == $name) {
                return $animal;
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

    public function askQuestion(string $prompt): string
    {
        $question = new Question($prompt);
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
}