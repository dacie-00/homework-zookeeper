<?php

namespace App\UI;

use Symfony\Component\Console\Output\OutputInterface;

class Table extends \Symfony\Component\Console\Helper\Table
{
    public function __construct(OutputInterface $output)
    {
        parent::__construct($output);
        $this->setStyle("box");
        $style = $this->getStyle();
        $style->setPadType(STR_PAD_BOTH);
        // can't set a default column width, so we just set it for 10 columns because we don't ever have any more
        $this->setColumnWidths([12, 12, 12, 12, 12, 12, 12, 12, 12, 12]);
    }
}