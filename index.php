<?php

require_once "vendor/autoload.php";

use App\AnimalParser;
use App\Game;
use Nette\Schema\ValidationException;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

$application = new Application();

$playCommand = new class extends Command {
    protected static $defaultName = 'start';


    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $animalDefinitions = [];
        $path = __DIR__ . "/app/animals";
        $dir = new DirectoryIterator($path);
        foreach ($dir as $fileInfo) {
            if (!$fileInfo->isDot()) {
                if ($fileInfo->getExtension() === 'json') {
                    $fileName = $fileInfo->getFilename();
                    $animalDefinitions[] = [
                        "data" => json_decode(file_get_contents($path . "/" . $fileName)),
                        "fileName" =>$fileName
                    ];
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        $filename = $animalDefinitions[count($animalDefinitions) - 1]['fileName'];
                        echo "Error - invalid animal definition in $filename: " . json_last_error_msg() . "\n";
                    }
                }
            }
        }
        $animals = [];
        foreach($animalDefinitions as &$animalDefinition) {
            try {
                $parsedAnimal = AnimalParser::parse($animalDefinition["data"]);
                $animals[$parsedAnimal->kind] = $parsedAnimal;
            } catch (ValidationException $e) {
                echo "Error - invalid animal definition in " . $animalDefinition["fileName"] . ": " . $e->getMessage() . "\n";
                exit();
            }
        }

        $game = new Game($input, $output, $animals);
        $game->run($input, $output);
        return Command::SUCCESS;
    }
};

$application->add($playCommand);
$application->setDefaultCommand('start', true); // Set 'play' as the default command

$application->run();