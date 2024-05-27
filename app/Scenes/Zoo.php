<?php

namespace App\Scenes;

use App\Game;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Question\ChoiceQuestion;

class Zoo
{
    private Game $game;

    public function __construct(Game $game)
    {
        $this->game = $game;
    }

    public function run()
    {
        $this->displayTable();
        while (true) {
            $action = $this->game->askChoiceQuestion("What do you want to do?", [
                "view zoo",
                "select animal",
                "view animal shop",
                "view food shop",
                "next turn"
            ]);
            if ($action == "view zoo") {
                $this->displayZooAnimalsTable();
                continue;
            }
            if ($action == "next turn") {
                $this->step();
                $this->displayZooAnimalsTable();
                continue;
            }
            if ($action == "view animal shop") {
                $this->state = self::STATE_ANIMAL_SHOP;
                break;
            }
            if ($action == "view food shop") {
                $this->state = self::STATE_FOOD_SHOP;
                break;
            }
            if ($action == "select animal") {
                $animalNames = [];
                foreach ($this->game->animals() as $animal) {
                    $animalNames[] = $animal->name();
                }
                $animalName = $this->game->askChoiceQuestion("Which animal?", $animalNames);
                $animal = $this->findAnimalByName($animalName);
                $this->displayAnimal($animal);
                $question = new ChoiceQuestion("select action?", ["feed", "play", "pet", "work", "idle"]);
                $answer = $helper->ask($this->consoleInput, $this->consoleOutput, $question);
                switch ($answer) {
                    case "feed";
                        $foodNames = [];
                        foreach ($this->foods as $food) {
                            $foodNames[] = $food->name();
                        }
//                        $animal->setAction([$animal, "eat"], 2, ["name" => "being fed apple", "food" => "apple"]);
                        break;
                }
            }
        }
    }

    private function displayTable()
    {
        $table = new Table($this->game->consoleOutput());
        $rows = [];
        $table->setHeaderTitle("Zoo");
        $table->setHeaders([
            "Name",
            "Kind",
            "Happiness",
            "Food Reserves",
            "Action",
            "<-- For turns"]);
        foreach ($this->game->animals() as $animal) {
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
}