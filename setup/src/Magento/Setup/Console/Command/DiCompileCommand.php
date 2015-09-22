<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Console\Command;

use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Module\ModuleRegistryInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\App\DeploymentConfig;
use Magento\Setup\Model\ObjectManagerProvider;
use Magento\Setup\Module\Di\App\Task\Manager;
use Magento\Setup\Module\Di\App\Task\OperationFactory;
use Magento\Setup\Module\Di\App\Task\OperationException;
use Magento\Setup\Module\Di\App\Task\OperationInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command to run compile in single-tenant mode
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DiCompileCommand extends Command
{
    /** @var DeploymentConfig */
    private $deploymentConfig;

    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var Manager */
    private $taskManager;

    /** @var DirectoryList */
    private $directoryList;
    
    /** @var  ModuleRegistryInterface */
    private $moduleRegistry;

    /** @var Filesystem */
    private $filesystem;

    /** @var array */
    private $excludedPathsList;

    /**
     * Constructor
     *
     * @param DeploymentConfig $deploymentConfig
     * @param DirectoryList $directoryList
     * @param Manager $taskManager
     * @param ObjectManagerProvider $objectManagerProvider
     * @param ModuleRegistryInterface $moduleRegistry
     * @param Filesystem $filesystem
     */
    public function __construct(
        DeploymentConfig $deploymentConfig,
        DirectoryList $directoryList,
        Manager $taskManager,
        ObjectManagerProvider $objectManagerProvider,
        ModuleRegistryInterface $moduleRegistry,
        Filesystem $filesystem
    ) {
        $this->deploymentConfig = $deploymentConfig;
        $this->directoryList    = $directoryList;
        $this->objectManager    = $objectManagerProvider->get();
        $this->taskManager      = $taskManager;
        $this->moduleRegistry   = $moduleRegistry;
        $this->filesystem       = $filesystem;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('setup:di:compile')
            ->setDescription(
                'Generates DI configuration and all non-existing interceptors and factories'
            );
        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $appCodePath = $this->directoryList->getPath(DirectoryList::MODULES);
        $libraryPath = $this->directoryList->getPath(DirectoryList::LIB_INTERNAL);
        $generationPath = $this->directoryList->getPath(DirectoryList::GENERATION);
        if (!$this->deploymentConfig->isAvailable()) {
            $output->writeln('You cannot run this command because the Magento application is not installed.');
            return;
        }
        $this->objectManager->get('Magento\Framework\App\Cache')->clean();
        $compiledPathsList = [
            'application' => $appCodePath,
            'library' => $libraryPath . '/Magento/Framework',
            'generated_helpers' => $generationPath
        ];
        $this->excludedPathsList = [
            'application' => '#^' . $appCodePath . '/[\\w]+/[\\w]+/Test#',
            'framework' => '#^' . $libraryPath . '/[\\w]+/[\\w]+/([\\w]+/)?Test#'
        ];
        $dataAttributesIncludePattern = [
            'extension_attributes' => '/\/etc\/([a-zA-Z_]*\/extension_attributes|extension_attributes)\.xml$/'
        ];
        $this->configureObjectManager($output);

        $operations = $this->getOperationsConfiguration(
            $compiledPathsList,
            $dataAttributesIncludePattern
        );
        
        $operations = $this->addModulePathsToOperations($operations);

        try {
            $this->cleanupFilesystem(
                [
                    DirectoryList::CACHE,
                    DirectoryList::GENERATION,
                    DirectoryList::DI,
                ]
            );
            foreach ($operations as $operationCode => $arguments) {
                $this->taskManager->addOperation(
                    $operationCode,
                    $arguments
                );
            }

            /** @var ProgressBar $progressBar */
            $progressBar = $this->objectManager->create(
                'Symfony\Component\Console\Helper\ProgressBar',
                [
                    'output' => $output,
                    'max' => count($operations)
                ]
            );
            $progressBar->setFormat(
                '<info>%message%</info> %current%/%max% [%bar%] %percent:3s%% %elapsed% %memory:6s%'
            );
            $output->writeln('<info>Compilation was started.</info>');
            $progressBar->start();
            $progressBar->display();

            $this->taskManager->process(
                function (OperationInterface $operation) use ($progressBar) {
                    $progressBar->setMessage($operation->getName() . '...');
                    $progressBar->display();
                },
                function (OperationInterface $operation) use ($progressBar) {
                    $progressBar->advance();
                }
            );

            $progressBar->finish();
            $output->writeln('');
            $output->writeln('<info>Generated code and dependency injection configuration successfully.</info>');
        } catch (OperationException $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
        }
    }

    /**
     * Delete directories by their code from "var" directory
     *
     * @param array $directoryCodeList
     * @return void
     */
    private function cleanupFilesystem($directoryCodeList)
    {
        foreach ($directoryCodeList as $code) {
            $this->filesystem->getDirectoryWrite($code)->delete();
        }
    }

    /**
     * Configure Object Manager
     *
     * @param OutputInterface $output
     * @return void
     */
    private function configureObjectManager(OutputInterface $output)
    {
        $this->objectManager->configure(
            [
                'preferences' => [
                    'Magento\Setup\Module\Di\Compiler\Config\WriterInterface' =>
                        'Magento\Setup\Module\Di\Compiler\Config\Writer\Filesystem',
                ],
                'Magento\Setup\Module\Di\Compiler\Config\ModificationChain' => [
                    'arguments' => [
                        'modificationsList' => [
                            'BackslashTrim' =>
                                ['instance' => 'Magento\Setup\Module\Di\Compiler\Config\Chain\BackslashTrim'],
                            'PreferencesResolving' =>
                                ['instance' => 'Magento\Setup\Module\Di\Compiler\Config\Chain\PreferencesResolving'],
                            'InterceptorSubstitution' =>
                                ['instance' => 'Magento\Setup\Module\Di\Compiler\Config\Chain\InterceptorSubstitution'],
                            'InterceptionPreferencesResolving' =>
                                ['instance' => 'Magento\Setup\Module\Di\Compiler\Config\Chain\PreferencesResolving'],
                            'ArgumentsSerialization' =>
                                ['instance' => 'Magento\Setup\Module\Di\Compiler\Config\Chain\ArgumentsSerialization'],
                        ]
                    ]
                ],
                'Magento\Setup\Module\Di\Code\Generator\PluginList' => [
                    'arguments' => [
                        'cache' => [
                            'instance' => 'Magento\Framework\App\Interception\Cache\CompiledConfig'
                        ]
                    ]
                ],
                'Magento\Setup\Module\Di\Code\Reader\ClassesScanner' => [
                    'arguments' => [
                        'excludePatterns' => $this->excludedPathsList
                    ]
                ],
                'Magento\Setup\Module\Di\Compiler\Log\Writer\Console' => [
                    'arguments' => [
                        'output' => $output,
                    ]
                ],
            ]
        );
    }

    /**
     * Returns operations configuration
     *
     * @param array $compiledPathsList
     * @param array $dataAttributesIncludePattern
     * @return array
     */
    private function getOperationsConfiguration(
        array $compiledPathsList,
        array $dataAttributesIncludePattern
    ) {
        $operations = [
            OperationFactory::REPOSITORY_GENERATOR => [
                'path' => $compiledPathsList['application'],
                'filePatterns' => ['di' => '/\/etc\/([a-zA-Z_]*\/di|di)\.xml$/']
            ],
            OperationFactory::DATA_ATTRIBUTES_GENERATOR => [
                'path' => $compiledPathsList['application'],
                'filePatterns' => $dataAttributesIncludePattern
            ],
            OperationFactory::APPLICATION_CODE_GENERATOR => [
                $compiledPathsList['application'],
                $compiledPathsList['library'],
                $compiledPathsList['generated_helpers'],
            ],
            OperationFactory::INTERCEPTION => [
                'intercepted_paths' => [
                    $compiledPathsList['application'],
                    $compiledPathsList['library'],
                    $compiledPathsList['generated_helpers'],
                ],
                'path_to_store' => $compiledPathsList['generated_helpers'],
            ],
            OperationFactory::AREA_CONFIG_GENERATOR => [
                $compiledPathsList['application'],
                $compiledPathsList['library'],
                $compiledPathsList['generated_helpers'],
            ],
            OperationFactory::INTERCEPTION_CACHE => [
                $compiledPathsList['application'],
                $compiledPathsList['library'],
                $compiledPathsList['generated_helpers'],
            ]
        ];

        return $operations;
    }

    /**
     * adds Paths from module Registry to Operations Array
     *
     * @param array $operations
     *
     * @return array
     */
    private function addModulePathsToOperations(array $operations)
    {
        $operationCodes = [
            OperationFactory::APPLICATION_CODE_GENERATOR,
            OperationFactory::AREA_CONFIG_GENERATOR,
            OperationFactory::INTERCEPTION_CACHE,
        ];
        
        $modulePaths = $this->moduleRegistry->getModulePaths();
        
        foreach ($operationCodes as $operationCode) {
            $operations[$operationCode] = array_merge($operations[$operationCode], $modulePaths);
        }
        
        $operations[OperationFactory::INTERCEPTION]['intercepted_paths'] = array_merge(
            $operations[OperationFactory::INTERCEPTION]['intercepted_paths'],
            $modulePaths
        );
        
        return $operations;
    }
}
