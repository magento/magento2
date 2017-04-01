<?php
/**
 * Created by PhpStorm.
 * @author Andra Lungu <andra.lungu@bitbull.it>
 * Date: 01/04/17
 * Time: 14.02
 */

namespace Magento\Developer\Console\Command;

use Magento\Developer\Model\Di\Information;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

class DiInfoCommand extends Command
{
    /**
     * Command name
     */
    const COMMAND_NAME = 'dev:di:info';

    /**
     * input name
     */
    const CLASS_NAME = 'class';

    /**
     * @var Information
     */
    private $diInformation;

    /**
     * @param Information $diInformation
     */
    public function __construct(
        Information $diInformation
    ) {
        $this->diInformation = $diInformation;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     * @throws InvalidArgumentException
     */
    protected function configure()
    {
        $this->setName(self::COMMAND_NAME)
             ->setDescription('Provides information on Dependency Injection configuration for the Command.')
             ->setDefinition([
                new InputArgument(self::CLASS_NAME, InputArgument::REQUIRED, 'Class name')
            ]);

        parent::configure();
    }

    /**
     * {@inheritdoc}
     * @throws \InvalidArgumentException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $className = $input->getArgument(self::CLASS_NAME);
        $output->writeln(sprintf('DI configuration for the class %s', $className));
        $output->writeln(sprintf('- Preference: %s', $this->diInformation->getPreference($className)));
        //$output->writeln(sprintf('- Constructor Parameters: %s', $this->diInformation->getPreference($className)));
        return \Magento\Framework\Console\Cli::RETURN_SUCCESS;
    }
}