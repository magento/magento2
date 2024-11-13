<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Developer\Console\Command;

use Magento\Developer\Model\Di\Information;
use Magento\Framework\ObjectManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\App\AreaList;
use Magento\Framework\App\Area;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DiInfoCommand extends Command
{
    /**
     * @var ObjectManagerInterface
     */
    private ObjectManagerInterface $objectManager;

    /**
     * Command name
     */
    public const COMMAND_NAME = 'dev:di:info';

    /**
     * input name
     */
    public const CLASS_NAME = 'class';

    /**
     * Area name
     */
    public const AREA_CODE = 'area';

    /**
     * @var Information
     */
    private Information $diInformation;

    /**
     * @var AreaList
     */
    private AreaList $areaList;

    /**
     * @param Information $diInformation
     * @param ObjectManagerInterface $objectManager
     * @param AreaList|null $areaList
     */
    public function __construct(
        Information            $diInformation,
        ObjectManagerInterface $objectManager,
        ?AreaList              $areaList = null
    ) {
        $this->diInformation = $diInformation;
        $this->objectManager = $objectManager;
        $this->areaList = $areaList ?? \Magento\Framework\App\ObjectManager::getInstance()->get(AreaList::class);
        parent::__construct();
    }

    /**
     * Initialization of the command
     *
     * @throws InvalidArgumentException
     */
    protected function configure()
    {
        $this->setName(self::COMMAND_NAME)
            ->setDescription('Provides information on Dependency Injection configuration for the Command.')
            ->setDefinition([
                new InputArgument(self::CLASS_NAME, InputArgument::REQUIRED, 'Class name'),
                new InputArgument(self::AREA_CODE, InputArgument::OPTIONAL, 'Area Code')
            ]);

        parent::configure();
    }

    /**
     * Print Info on Class/Interface preference
     *
     * @param string $className
     * @param OutputInterface $output
     * @return void
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
        $paramsTable->render();
    }

    /**
     * Print Info on Virtual Types
     *
     * @param string $className
     * @param OutputInterface $output
     * @return void
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

        $table->render();
    }

    /**
     * Displays dependency injection configuration information for a class.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $area = $input->getArgument(self::AREA_CODE) ?? Area::AREA_GLOBAL;
        if ($area !== Area::AREA_GLOBAL) {
            $this->setDiArea($area);
        }
        $className = $input->getArgument(self::CLASS_NAME);
        $output->setDecorated(true);
        $output->writeln('');
        $output->writeln(sprintf('DI configuration for the class %s in the %s area', $className, strtoupper($area)));

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

    /**
     * Set Area for DI Configuration
     *
     * @param string $area
     * @return void
     * @throws \InvalidArgumentException
     */
    private function setDiArea(string $area): void
    {
        if ($this->validateAreaCodeFromInput($area)) {
            $areaOmConfiguration = $this->objectManager
                ->get(\Magento\Framework\App\ObjectManager\ConfigLoader::class)
                ->load($area);

            $this->objectManager->configure($areaOmConfiguration);

            $this->objectManager->get(\Magento\Framework\Config\ScopeInterface::class)
                ->setCurrentScope($area);
        } else {
            throw new InvalidArgumentException(sprintf('The "%s" area code does not exist', $area));
        }
    }

    /**
     * Validate Input
     *
     * @param string $area
     * @return bool
     */
    private function validateAreaCodeFromInput($area): bool
    {
        $availableAreaCodes = $this->areaList->getCodes();
        return in_array($area, $availableAreaCodes, true);
    }
}
