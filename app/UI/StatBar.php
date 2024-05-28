<?php
declare(strict_types=1);

namespace App\UI;

use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;

class StatBar
{
    public static function display(
        OutputInterface $output,
        string          $name,
        int             $value,
        int             $max = 1000,
        string          $color = null): ProgressBar
    {
        if ($color == null) {
            $color = self::getColorByProportion($value, $max);
        }
        $displayBar = new ProgressBar($output, $max);
        $displayBar->setFormat("$name [<fg=$color>%bar%</>]\n");
        $displayBar->setProgress($value);
        return $displayBar;
    }

    private static function getColorByProportion(int $value, int $max = 100): string
    {
        $ratio = $value / $max;
        if ($ratio > 0.66) {
            return "green";
        }
        if ($ratio > 0.33) {
            return "yellow";
        }
        return "red";
    }
}