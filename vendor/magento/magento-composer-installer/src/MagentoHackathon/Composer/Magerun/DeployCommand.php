<?php

namespace MagentoHackathon\Composer\Magerun;

use N98\Magento\Command\AbstractMagentoCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DeployCommand extends AbstractMagentoCommand
{
    protected function configure()
    {
      $this
          ->setName('composer:magento:deploy')
          ->setDescription('Test command registered in a module')
      ;
    }

   /**
    * @param \Symfony\Component\Console\Input\InputInterface $input
    * @param \Symfony\Component\Console\Output\OutputInterface $output
    * @return int|void
    */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        
        $output->writeln('it works, maybe');
    }
}
