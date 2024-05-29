<?php
declare(strict_types=1);

namespace App\Scenes;

use App\Game;

class FoodStorage
{
    private Game $game;

    public function __construct(Game $game)
    {
        $this->game = $game;
    }

    public function run(): void
    {
        if (count($this->game->foods()) == 0) {
            $this->game->addMessage("You have no food in your storage!");
            $this->game->setState($this->game::STATE_ZOO);
            return;
        }
        $this->displayTable();
        readline("Return to zoo");
        $this->game->setState($this->game::STATE_ZOO);
    }

    private function displayTable(): void
    {
        $table = new \App\UI\Table($this->game->consoleOutput());
        $rows = [];
        $table->setHeaderTitle("Food Storage");
        $table->setHeaders([
            "Food",
            "Quantity",
        ]);
        foreach ($this->game->foods() as $food => $quantity) {
            $rows[] = [
                $food,
                $quantity,
            ];
        }
        $table->setRows($rows);
        $table->render();
    }
}