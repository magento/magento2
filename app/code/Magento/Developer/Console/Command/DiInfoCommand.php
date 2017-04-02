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

        $output->writeln(
            sprintf('It is Virtual Type for the Class %s', $this->diInformation->getVirtualTypeBase($className))
        );

        $preference = $this->diInformation->getPreference($className);
        $output->writeln('');
        $output->writeln(sprintf('Preference: %s', $preference));
        $output->writeln('');
        $output->writeln("Constructor Parameters:");
        $paramsTable = new Table($output);
        $paramsTable
            ->setHeaders(['Name', 'Type', 'Configured Value']);
        $parameters = $this->diInformation->getParameters($className);
        $paramsTableArray = [];
        foreach ($parameters as $parameterRow) {
            if (is_array($parameterRow[2])) {
                $parameterRow[2] = json_encode($parameterRow[2], JSON_PRETTY_PRINT);
            }
            $paramsTableArray[] = $parameterRow;
        }
        $paramsTable->setRows($paramsTableArray);
        $output->writeln($paramsTable->render());

        $virtualTypes = $this->diInformation->getVirtualTypes($preference);
        if (!empty($virtualTypes)) {
            $output->writeln('');
            $output->writeln("Virtual Types:");
            foreach ($this->diInformation->getVirtualTypes($className) as $virtualType) {
                $output->writeln('   ' . $virtualType);
            }
        }

        $output->writeln('');
        $output->writeln("Plugins:");
        $plugins = $this->diInformation->getPlugins($className);
        $parameters = [];
        foreach ($plugins as $type => $plugin) {
            foreach ($plugin as $instance => $pluginMethods){
                foreach ($pluginMethods as $pluginMethod){
                    $parameters[] = [$instance, $pluginMethod, $type];
                }
            }
        }

        $table = new Table($output);
        $table
            ->setHeaders(array('Plugin', 'Method', 'Type'))
            ->setRows($parameters);

        $output->writeln($table->render());

        $output->writeln('');
        $output->writeln("Preference Plugins:");
        $plugins = $this->diInformation->getPlugins($preference);
        $parameters = [];
        foreach ($plugins as $type => $plugin) {
            foreach ($plugin as $instance => $pluginMethods){
                foreach ($pluginMethods as $pluginMethod){
                    $parameters[] = [$instance, $pluginMethod, $type];
                }
            }
        }

        $table = new Table($output);
        $table
            ->setHeaders(array('Plugin', 'Method', 'Type'))
            ->setRows($parameters);

        $output->writeln($table->render());

        return \Magento\Framework\Console\Cli::RETURN_SUCCESS;
    }
}