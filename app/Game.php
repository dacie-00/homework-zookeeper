<?php
declare(strict_types=1);

namespace App;


use App\UI\StatBar;
use stdClass;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;

class Game
{
    private const STATE_ANIMAL_SHOP = "animal shop";
    private const STATE_BUY_FOOD = "buy food";
    private const STATE_ZOO = "zoo";
    private array $animalTypes;
    /**
     * @var Animal[]
     */
    private array $animals = [];
    private $input;
    private $output;
    private string $state;

    public function __construct(InputInterface $input, OutputInterface $output, array $animalTypes)
    {
        $this->input = $input;
        $this->output = $output;
        $this->state = self::STATE_ANIMAL_SHOP;
        $this->animalTypes = $animalTypes;
    }

    public function displayAnimal(Animal $animal): void
    {
        $actionName = $animal->actionName();
        echo "{$animal->name()} the {$animal->kind()} (currently $actionName)\n";

        $foodReserves = $animal->foodReserves();
        $happiness = $animal->happiness();

        StatBar::display($this->output, "Food Reserves", $foodReserves);
        StatBar::display($this->output, "Happiness    ", $happiness);
    }

    public function run($input, $output)
    {
        while (true) {
            if ($this->state == self::STATE_ANIMAL_SHOP) {
                $this->animalShop();
            }
            if ($this->state == self::STATE_ZOO) {
                $this->zoo();
            }
        }
    }

    private function animalShop()
    {
        $this->displayShopAnimalsTable();
        while (true) {
            $helper = new QuestionHelper();
            $question = new ChoiceQuestion("What do you want to do?", ["display available animals", "purchase animal", "exit shop"]);
            $answer = $helper->ask($this->input, $this->output, $question);
            if ($answer == "exit shop") {
                $this->state = self::STATE_ZOO;
                return;
            }
            if ($answer == "purchase animal") {
                $animalNames = array_column($this->animalTypes, "kind");
                $question = new ChoiceQuestion("Which animal?", $animalNames);
                $answer = $helper->ask($this->input, $this->output, $question);

                $newAnimal = $this->addAnimal($this->animalTypes[$answer]);
                $question = new Question("What will the {$newAnimal->kind()}'s name be? ");
                $name = $helper->ask($this->input, $this->output, $question);
                $newAnimal->setName($name);
                echo "The new animal has been added to your zoo!\n";
            }
        }
    }

    private function displayShopAnimalsTable(): void
    {
        $table = new Table($this->output);
        $rows = [];
        $table->setHeaderTitle("Animal Shop");
        $table->setHeaders(["Species", "Price"]);
        foreach ($this->animalTypes as $animal) {
            $rows[] = [$animal->kind, "$animal->price$"];
        }
        $table->setStyle('box');
        $table->setRows($rows);
        $table->render();
    }

    public function addAnimal(stdClass $properties): Animal
    {
        $animal = new Animal($properties);
        $this->animals[] = $animal;
        return $animal;
    }

    private function zoo(): void
    {
        $this->displayZooAnimalsTable();
        while (true) {
            $helper = new QuestionHelper();
            $question = new ChoiceQuestion("What do you want to do?", [
                "view zoo",
                "select animal",
                "view animal shop",
                "next turn"
            ]);
            $answer = $helper->ask($this->input, $this->output, $question);
            if ($answer == "view zoo") {
                $this->displayZooAnimalsTable();
                continue;
            }
            if ($answer == "next turn") {
                $this->step();
                $this->displayZooAnimalsTable();
                continue;
            }
            if ($answer == "view animal shop") {
                $this->state = self::STATE_ANIMAL_SHOP;
                break;
            }
            if ($answer == "select animal") {
                $animalNames = [];
                foreach ($this->animals as $animal) {
                    $animalNames[] = $animal->name();
                }
                $question = new ChoiceQuestion("Which animal?", $animalNames);
                $answer = $helper->ask($this->input, $this->output, $question);
                $animal = $this->findAnimalByName($answer);
                $this->displayAnimal($animal);
                $question = new ChoiceQuestion("select action?", ["feed", "play", "pet", "work", "idle"]);
                $answer = $helper->ask($this->input, $this->output, $question);
                switch ($answer) {
                    case "feed";
                    $animal->setAction([$animal, "eat"], 2, ["name" => "being fed apple", "food" => "apple"]);
                }
            }
        }
    }

    private function displayZooAnimalsTable()
    {
        $table = new Table($this->output);
        $rows = [];
        $table->setHeaderTitle("Zoo");
        $table->setHeaders([
            "Name",
            "Kind",
            "Happiness",
            "Food Reserves",
            "Action",
            "<-- For turns"]);
        foreach ($this->animals as $animal) {
            $rows[] = [
                $animal->name(),
                $animal->kind(),
                $animal->happiness(),
                $animal->foodReserves(),
                $animal->actionName(),
                $animal->action()->times()
            ];
        }
        $table->setStyle('box');
        $table->setRows($rows);
        $table->render();
    }

    private function displayBar(string $name, int $value, int $max, $color = "bright-white"): ProgressBar
    {
        $displayBar = new ProgressBar($this->output, $max);
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
}