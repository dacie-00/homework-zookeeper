<?php

namespace App\Scenes;

use App\Game;
use Symfony\Component\Console\Helper\Table;

class FoodStorage
{
    private Game $game;

    public function __construct(Game $game)
    {
        $this->game = $game;
    }

    public function run()
    {
        $this->displayTable();
        readline("Return to zoo");
        $this->game->setState($this->game::STATE_ZOO);
    }

    private function displayTable(): void
    {
        $table = new Table($this->game->consoleOutput());
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
        $table->setStyle('box');
        $table->setRows($rows);
        $table->render();
    }
}