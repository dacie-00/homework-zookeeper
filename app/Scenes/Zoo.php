<?php

namespace App\Scenes;

use App\Game;
use App\UI\StatBar;
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

    public function run(): void
    {
        $this->displayTable();
        while (true) {
            echo "You have {$this->game->money()}$\n";
            $action = $this->game->askChoiceQuestion("What do you want to do?", [
                "view zoo",
                "select animal",
                "animal shop",
                "food shop",
                "food storage",
                "next turn"
            ]);
            if (count($this->game->animals()) == 0 && ($action == "select animal")) {
                echo "Cannot select any animal (you don't have any)\n";
                continue;
            }
            if ($action == "view zoo") {
                $this->displayTable();
                continue;
            }
            if ($action == "next turn") {
                $this->game->step();
                $this->displayTable();
                continue;
            }
            if ($action == "animal shop") {
                $this->game->setState($this->game::STATE_ANIMAL_SHOP);
                return;
            }
            if ($action == "food shop") {
                $this->game->setState($this->game::STATE_FOOD_SHOP);
                return;
            }
            if ($action == "food storage") {
                $this->game->setState($this->game::STATE_FOOD_STORAGE);
                return;
            }
            if ($action == "select animal") {
                $animalNames = [];
                foreach ($this->game->animals() as $animal) {
                    $animalNames[] = $animal->name();
                }
                $animalName = $this->game->askChoiceQuestion("Which animal?", $animalNames);
                $animal = $this->game->findAnimalByName($animalName);
                $this->game->setState($this->game::STATE_ANIMAL_MENU);
                $animalMenu = new AnimalMenu($this->game, $animal);
                $animalMenu->run();
                $this->displayTable();
            }
        }
    }

    private function displayTable(): void
    {
        if (count($this->game->animals()) == 0) {
            echo "You have no animals in your zoo! Buy some!\n";
            return;
        }
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
                $animal->action()->times() < 100000 ? $animal->action()->times() : "forever"
            ];
        }
        $table->setStyle('box');
        $table->setRows($rows);
        $table->render();
    }
}