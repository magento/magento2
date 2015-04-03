<?php
namespace Magento\Setup\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;

abstract class AbstractSetupCommand extends Command
{
    protected function configure()
    {
        $this->addOption(
            'magento_init_params',
            null,
            InputOption::VALUE_REQUIRED,
            'Magento initialization parameters'
        );
    }
}
