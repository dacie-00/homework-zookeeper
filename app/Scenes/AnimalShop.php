<?php

namespace App\Scenes;

use App\Game;

class AnimalShop
{
    private Game $game;

    public function __construct(Game $game)
    {
        $this->game = $game;
    }

    public function run(): void
    {
        $messages = [];
        while (true) {
            $this->displayTable();
            echo "You have {$this->game->money()}$\n";
            if (!empty($messages)) {
                foreach ($messages as $message) {
                    echo $message . "\n";
                }
            }
            $messages = [];
            $action = $this->game->askChoiceQuestion("What do you want to do?", [
                "display available animals",
                "purchase animal",
                "exit shop"
            ]);
            if ($action === "display available animals") {
                continue;
            }
            if ($action == "purchase animal") {
                $animalNames = array_column($this->game->animalTypes(), "kind");
                $animal = $this->game->askChoiceQuestion("Which animal?", $animalNames);
                $animal = $this->game->animalTypes()[$animal];
                if ($animal->price > $this->game->money()) {
                    $messages[] = "You cannot afford the $animal->kind!";
                    continue;
                }
                $this->game->decrementMoney($animal->price);
                $newAnimal = $this->game->addAnimal($animal);
                $name = $this->game->askQuestion("What will the {$newAnimal->kind()}'s name be? ");
                $newAnimal->setName($name);
                $messages[] = "{$newAnimal->name()} the {$newAnimal->kind()} has been added to your zoo!";
                continue;
            }
            if ($action == "exit shop") {
                $this->game->setState($this->game::STATE_ZOO);
                return;
            }
        }
    }

    private function displayTable(): void
    {
        $table = new \App\UI\Table($this->game->consoleOutput());
        $rows = [];
        $table->setHeaderTitle("Animal Shop");
        $table->setHeaders(["Species", "Price"]);
        foreach ($this->game->animalTypes() as $animal) {
            $rows[] = [$animal->kind, "$animal->price$"];
        }
        $table->setRows($rows);
        $table->render();
    }
}