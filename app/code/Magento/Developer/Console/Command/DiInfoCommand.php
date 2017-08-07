<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Developer\Console\Command;

use Magento\Developer\Model\Di\Information;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Helper\Table;

/**
 * Class \Magento\Developer\Console\Command\DiInfoCommand
 *
 * @since 2.2.0
 */
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
     * @since 2.2.0
     */
    private $diInformation;

    /**
     * @param Information $diInformation
     * @since 2.2.0
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
     * @since 2.2.0
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
     * Print Info on Class/Interface preference
     *
     * @param string $className
     * @param OutputInterface $output
     * @return void
     * @since 2.2.0
     */
    private function printPreference($className, $output)
    {
        $preference = $this->diInformation->getPreference($className);
        $output->writeln('');
        $output->writeln(sprintf('Preference: %s', $preference));
        $output->writeln('');
    }

    /**
     * Print Info on Constructor Arguments
     *
     * @param string $className
     * @param OutputInterface $output
     * @return void
     * @since 2.2.0
     */
    private function printConstructorArguments($className, $output)
    {
        $output->writeln("Constructor Parameters:");
        $paramsTable = new Table($output);
        $paramsTable
            ->setHeaders(['Name', 'Requested Type', 'Configured Value']);
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
    }

    /**
     * Print Info on Virtual Types
     *
     * @param string $className
     * @param OutputInterface $output
     * @return void
     * @since 2.2.0
     */
    private function printVirtualTypes($className, $output)
    {
        $virtualTypes = $this->diInformation->getVirtualTypes($className);
        if (!empty($virtualTypes)) {
            $output->writeln('');
            $output->writeln("Virtual Types:");
            foreach ($this->diInformation->getVirtualTypes($className) as $virtualType) {
                $output->writeln('   ' . $virtualType);
            }
        }
    }

    /**
     * Print Info on Plugins
     *
     * @param string $className
     * @param OutputInterface $output
     * @param string $label
     * @return void
     * @since 2.2.0
     */
    private function printPlugins($className, $output, $label)
    {
        $output->writeln('');
        $output->writeln($label);
        $plugins = $this->diInformation->getPlugins($className);
        $parameters = [];
        foreach ($plugins as $type => $plugin) {
            foreach ($plugin as $instance => $pluginMethods) {
                foreach ($pluginMethods as $pluginMethod) {
                    $parameters[] = [$instance, $pluginMethod, $type];
                }
            }
        }

        $table = new Table($output);
        $table
            ->setHeaders(['Plugin', 'Method', 'Type'])
            ->setRows($parameters);

        $output->writeln($table->render());
    }

    /**
     * {@inheritdoc}
     * @throws \InvalidArgumentException
     * @since 2.2.0
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $className = $input->getArgument(self::CLASS_NAME);
        $output->setDecorated(true);
        $output->writeln('');
        $output->writeln(sprintf('DI configuration for the class %s in the GLOBAL area', $className));

        if ($this->diInformation->isVirtualType($className)) {
            $output->writeln(
                sprintf('It is Virtual Type for the Class %s', $this->diInformation->getVirtualTypeBase($className))
            );
        }
        $this->printPreference($className, $output);
        $this->printConstructorArguments($className, $output);
        $preference = $this->diInformation->getPreference($className);
        $this->printVirtualTypes($preference, $output);
        $this->printPlugins($className, $output, 'Plugins:');
        $this->printPlugins($preference, $output, 'Plugins for the Preference:');

        return \Magento\Framework\Console\Cli::RETURN_SUCCESS;
    }
}
