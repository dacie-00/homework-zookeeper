<?php
declare(strict_types=1);

namespace App\Scenes;

use App\Game;
use RuntimeException;

class FoodShop
{
    private Game $game;

    public function __construct(Game $game)
    {
        $this->game = $game;
    }

    public function run(): void
    {
        while (true) {
            $this->displayTable();
            echo "You have {$this->game->money()}$\n";
            $this->game->echoMessages();
            $this->game->clearMessages();
            $action = $this->game->askChoiceQuestion("What do you want to do?", [
                "display available foods",
                "purchase food",
                "exit shop"
            ]);
            if ($action === "display available foods") {
                continue;
            }
            if ($action == "purchase food") {
                $foodNames = [];
                foreach ($this->game->foodTypes() as $foodType) {
                    $foodNames[] = $foodType->name();
                }
                $food = $this->game->askChoiceQuestion("Which food?", $foodNames);
                $food = $this->game->foodTypes()[$food];
                if ($food->price() > $this->game->money()) {
                    $this->game->addMessage("You cannot afford the {$food->name()}!");
                    continue;
                }
                $money = $this->game->money();
                $price = $food->price();
                $quantity = $this->game->askQuestion("Enter your desired quantity (n to cancel) \n > ",
                    function (string $quantity) use ($money, $price): string {
                        if ($quantity == "n") {
                            return $quantity;
                        }
                        if (!is_numeric($quantity)) {
                            throw new RuntimeException("Quantity must be a number");
                        }
                        if ($quantity < 1) {
                            throw new RuntimeException("Quantity must be greater than 0");
                        }
                        if ($price * $quantity > $money) {
                            throw new RuntimeException("You cannot afford so many!");
                        }
                        return $quantity;
                    }
                );
                if ($quantity == "n") {
                    continue;
                }
                $quantity = (int)$quantity;
                $this->game->decrementMoney($food->price() * $quantity);
                $this->game->addFood($food, $quantity);
                $this->game->addMessage("{$food->name()} has been added to your food storage!");
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
        $table->setHeaderTitle("Food Shop");
        $table->setHeaders(["Food", "Price"]);
        foreach ($this->game->foodTypes() as $food) {
            $rows[] = [$food->name(), "{$food->price()}$"];
        }
        $table->setRows($rows);
        $table->render();
    }
}