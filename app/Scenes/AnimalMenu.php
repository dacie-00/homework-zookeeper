<?php

namespace App\Scenes;

use App\Animal;
use App\Game;
use App\UI\StatBar;

class AnimalMenu
{
    private Game $game;
    private Animal $animal;

    public function __construct(Game $game, Animal $animal)
    {
        $this->game = $game;
        $this->animal = $animal;
    }

    public function run(): void
    {
        $this->displayAnimal($this->animal);
        $action = $this->game->askChoiceQuestion("Select action", ["feed", "play", "pet", "work", "idle"]);
        $turnCount = $this->game->askQuestion("For how many turns? \n > ", function (string $turnCount) {
            if (!is_numeric($turnCount)) {
                throw new \RuntimeException("Turn count must be a number");
            }
            if ($turnCount < 1 || $turnCount > 100) {
                throw new \RuntimeException("Turn count can only be in the range of 1 to 100");
            }
            return $turnCount;
        });
        $turnCount = (int) $turnCount;

        switch ($action) {
            case "feed";
                $foodNames = [];
                foreach ($this->game->foods() as $food => $count) {
                    $foodNames[] = $food;
                }
                if (count($foodNames) < 1) {
                    echo "You don't have any food!";
                    return;
                }
                $foodName = $this->game->askChoiceQuestion("Select food to give", $foodNames);
                $food = $this->game->findFoodByName($foodName);
                $this->animal->setAction([$this->animal, "eat"], $turnCount, ["name" => "being fed {$food->name()}", "food" => $food]);
                return;
            case "play";
                $this->animal->setAction([$this->animal, "play"], $turnCount, ["name" => "playing"]);
                return;
            case "pet";
                $this->animal->setAction([$this->animal, "pet"], $turnCount, ["name" => "being pet"]);
                return;
            case "work";
                $this->animal->setAction([$this->animal, "work"], $turnCount, ["name" => "working"]);
                return;
            case "idle";
                $this->animal->setAction([$this->animal, "idle"], $turnCount, ["name" => "idling"]);
                return;
        }

    }

    private function displayAnimal($animal)
    {
        $actionName = $animal->actionName();
        echo "{$animal->name()} the {$animal->kind()} (currently $actionName)\n";

        $foodReserves = $animal->foodReserves();
        $happiness = $animal->happiness();

        StatBar::display($this->game->consoleOutput(), "Food Reserves", $foodReserves);
        StatBar::display($this->game->consoleOutput(), "Happiness    ", $happiness);
    }

}