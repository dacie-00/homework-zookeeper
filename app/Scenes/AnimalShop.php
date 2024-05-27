<?php

namespace App\Scenes;

use App\Game;
use Symfony\Component\Console\Helper\Table;

class AnimalShop
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
            $action = $this->game->askChoiceQuestion("What do you want to do?", [
                "display available animals",
                "purchase animal",
                "exit shop"
            ]);
            if ($action === "display available animals") {
                $this->displayTable();
                continue;
            }
            if ($action == "purchase animal") {
                $animalNames = array_column($this->game->animalTypes(), "kind");
                $animal = $this->game->askChoiceQuestion("Which animal?", $animalNames);
                $newAnimal = $this->game->addAnimal($this->game->animalTypes()[$animal]);

                $name = $this->game->askQuestion("What will the {$newAnimal->kind()}'s name be? ");
                $newAnimal->setName($name);
                echo "The new animal has been added to your zoo!\n";
                $this->displayTable();
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
        $table = new Table($this->game->consoleOutput());
        $rows = [];
        $table->setHeaderTitle("Animal Shop");
        $table->setHeaders(["Species", "Price"]);
        foreach ($this->game->animalTypes() as $animal) {
            $rows[] = [$animal->kind, "$animal->price$"];
        }
        $table->setStyle('box');
        $table->setRows($rows);
        $table->render();
    }
}