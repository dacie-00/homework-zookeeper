<?php

require_once "vendor/autoload.php";

use App\Animal;
use App\AnimalParser;
use App\Food;
use App\FoodParser;
use App\Game;
use App\ItemParser;
use Nette\Schema\ValidationException;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

$application = new Application();

$playCommand = new class extends Command {
    protected static $defaultName = 'start';

    private function getFilesInFolder(string $path, ?string $extension = null): array
    {
        $files = [];
        $dir = new DirectoryIterator($path);
        foreach ($dir as $fileInfo) {
            if (!$fileInfo->isDot()) {
                if ($extension == null || $fileInfo->getExtension() === $extension) {
                    $fileName = $fileInfo->getFilename();
                    $files[] = $path . "/" . $fileName;
                }
            }
        }
        return $files;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $animalDefinitions = $this->getFilesInFolder(__DIR__ . "/app/animals", "json");

        $animals = [];
        $animalParser = new ItemParser(Animal::schema());
        foreach ($animalDefinitions as $animalDefinition) {
            try {
                $parsedAnimal = $animalParser->parse($animalDefinition);
                $animals[$parsedAnimal->kind] = $parsedAnimal;
            } catch (ValidationException $e) {
                echo "Error - invalid animal definition in " . basename($animalDefinition) . ": " . $e->getMessage() . "\n";
                exit();
            } catch (JsonException $e) {
                echo "Error - invalid animal definition in " . basename($animalDefinition) . ": " . $e->getMessage() . "\n";
                exit();
            }
        }

        $foodDefinitions = $this->getFilesInFolder(__DIR__ . "/app/foods", "json");

        $foods = [];
        $foodParser = new ItemParser(Food::schema());
        foreach ($foodDefinitions as $foodDefinition) {
            try {
                $parsedFood = $foodParser->parse($foodDefinition);
                $foods[$parsedFood->name] = $parsedFood;
            } catch (ValidationException $e) {
                echo "Error - invalid food definition in " . basename($foodDefinition) . ": " . $e->getMessage() . "\n";
                exit();
            } catch (JsonException $e) {
                echo "Error - invalid food definition in " . basename($foodDefinition) . ": " . $e->getMessage() . "\n";
                exit();
            }
        }

        $game = new Game($input, $output, $animals, $foods);
        $game->run($input, $output);
        return Command::SUCCESS;
    }
};

$application->add($playCommand);
$application->setDefaultCommand('start', true); // Set 'play' as the default command

$application->run();