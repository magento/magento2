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
use Symfony\Component\Console\Helper\Table;

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
        $output->writeln('');
        $output->writeln(sprintf('DI configuration for the class %s', $className));
        $output->writeln('');
        $output->writeln(sprintf('Preference: %s', $this->diInformation->getPreference($className)));
        $output->writeln('');
        $output->writeln("Constructor Parameters:");
        $parameters = [];
        $diParametersConfiguration = $this->diInformation->getConfiguredConstructorParameters($className);
        foreach ($this->diInformation->getConstructorParameters($className) as $parameter) {
            $paramArray = [$parameter[0], $parameter[1], ''];
            if (isset($diParametersConfiguration[$parameter[0]])) {
                $paramArray[2] = $diParametersConfiguration[$parameter[0]]['instance'];
            }
            $parameters[] = $paramArray;
        }
        $table = new Table($output);
        $table
            ->setHeaders(array('Name', 'Type', 'Configured Type'))
            ->setRows($parameters);

        $output->writeln($table->render());
        $output->writeln('');
        $output->writeln("Virtual Types:");
        foreach ($this->diInformation->getVirtualTypes($className) as $virtualType) {
            $output->writeln('   ' . $virtualType);
        }

        return \Magento\Framework\Console\Cli::RETURN_SUCCESS;
    }
}