<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

namespace Magento\Setup\Console\Command;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Interception\Cache\CompiledConfig;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ObjectManager\ConfigWriterInterface;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Config\ConfigOptionsListConstants;
use Magento\Framework\Console\Cli;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\DriverInterface;
use Magento\Framework\Filesystem\Io\File;
use Magento\Setup\Model\ObjectManagerProvider;
use Magento\Setup\Module\Di\App\Task\Manager;
use Magento\Setup\Module\Di\App\Task\OperationException;
use Magento\Setup\Module\Di\App\Task\OperationFactory;
use Magento\Setup\Module\Di\App\Task\OperationInterface;
use Magento\Setup\Module\Di\Code\Generator\PluginList;
use Magento\Setup\Module\Di\Code\Reader\ClassesScanner;
use Magento\Setup\Module\Di\Compiler\Config\Chain\BackslashTrim;
use Magento\Setup\Module\Di\Compiler\Config\Chain\InterceptorSubstitution;
use Magento\Setup\Module\Di\Compiler\Config\Chain\PreferencesResolving;
use Magento\Setup\Module\Di\Compiler\Config\ModificationChain;
use Magento\Setup\Module\Di\Compiler\Log\Writer\Console;
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
    public const NAME = 'setup:di:compile';

    /**
     * @var \Magento\Framework\App\DeploymentConfig
     */
    private $deploymentConfig;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var \Magento\Setup\Module\Di\App\Task\Manager
     */
    private $taskManager;

    /**
     * @var \Magento\Framework\App\Filesystem\DirectoryList
     */
    private $directoryList;

    /**
     * @var \Magento\Framework\Filesystem
     */
    private $filesystem;

    /**
     * @var array
     */
    private $excludedPathsList;

    /**
     * @var \Magento\Framework\Filesystem\DriverInterface
     */
    private $fileDriver;

    /**
     * @var ComponentRegistrar
     */
    private $componentRegistrar;

    /**
     * @var File
     */
    private $file;

    /**
     * @param DeploymentConfig $deploymentConfig
     * @param DirectoryList $directoryList
     * @param Manager $taskManager
     * @param ObjectManagerProvider $objectManagerProvider
     * @param Filesystem $filesystem
     * @param DriverInterface $fileDriver
     * @param \Magento\Framework\Component\ComponentRegistrar $componentRegistrar
     * @param File|null $file
     *
     * @throws \Magento\Setup\Exception
     */
    public function __construct(
        DeploymentConfig $deploymentConfig,
        DirectoryList $directoryList,
        Manager $taskManager,
        ObjectManagerProvider $objectManagerProvider,
        Filesystem $filesystem,
        DriverInterface $fileDriver,
        ComponentRegistrar $componentRegistrar,
        File|null $file = null
    ) {
        $this->deploymentConfig = $deploymentConfig;
        $this->directoryList    = $directoryList;
        $this->objectManager    = $objectManagerProvider->get();
        $this->taskManager      = $taskManager;
        $this->filesystem       = $filesystem;
        $this->fileDriver       = $fileDriver;
        $this->componentRegistrar  = $componentRegistrar;
        $this->file = $file ?: ObjectManager::getInstance()->get(File::class);
        parent::__construct();
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName(self::NAME)
            ->setDescription(
                'Generates DI configuration and all missing classes that can be auto-generated'
            );
        parent::configure();
    }

    /**
     * Checks that application is installed and DI resources are cleared
     *
     * @return string[]
     */
    private function checkEnvironment()
    {
        $messages = [];
        $config = $this->deploymentConfig->get(ConfigOptionsListConstants::KEY_MODULES);
        if (!$config) {
            $messages[] = 'You cannot run this command because modules are not enabled. You can enable modules by'
             . ' running the \'module:enable --all\' command.';
        }

        return $messages;
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $errors = $this->checkEnvironment();
        if ($errors) {
            foreach ($errors as $line) {
                $output->writeln($line);
            }

            // we must have an exit code higher than zero to indicate something was wrong
            return Cli::RETURN_FAILURE;
        }

        $modulePaths = $this->componentRegistrar->getPaths(ComponentRegistrar::MODULE);
        $moduleStatuses = $this->deploymentConfig->get(ConfigOptionsListConstants::KEY_MODULES);

        $modulePathsEnabled = array_filter($modulePaths, function ($path, $module) use ($moduleStatuses) {
            return ($moduleStatuses[$module] ?? 0) === 1;
        }, ARRAY_FILTER_USE_BOTH);

        $libraryPaths = $this->componentRegistrar->getPaths(ComponentRegistrar::LIBRARY);
        $setupPath = $this->directoryList->getPath(DirectoryList::SETUP);
        $generationPath = $this->directoryList->getPath(DirectoryList::GENERATED_CODE);

        $this->objectManager->get(\Magento\Framework\App\Cache::class)->clean();
        $compiledPathsList = [
            'application' => $modulePathsEnabled,
            'library' => $libraryPaths,
            'setup' => $setupPath,
            'generated_helpers' => $generationPath
        ];

        $this->excludedPathsList = [
            'application' => $this->getExcludedModulePaths($modulePathsEnabled),
            'framework' => $this->getExcludedLibraryPaths($libraryPaths),
            'setup' => $this->getExcludedSetupPaths($setupPath),
        ];
        $this->configureObjectManager($output);

        $operations = $this->getOperationsConfiguration($compiledPathsList);

        try {
            $this->cleanupFilesystem(
                [
                    DirectoryList::CACHE,
                    DirectoryList::GENERATED_METADATA,
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
                \Symfony\Component\Console\Helper\ProgressBar::class,
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
                function (OperationInterface $operation) use ($progressBar): void {
                    $progressBar->setMessage($operation->getName() . '...');
                    $progressBar->display();
                },
                function (OperationInterface $operation) use ($progressBar): void {
                    $progressBar->advance();
                }
            );

            $progressBar->finish();
            $output->writeln('');
            $output->writeln('<info>Generated code and dependency injection configuration successfully.</info>');
        } catch (OperationException $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            // we must have an exit code higher than zero to indicate something was wrong
            return Cli::RETURN_FAILURE;
        }

        return Cli::RETURN_SUCCESS;
    }

    /**
     * Build list of module path regexps which should be excluded from compilation
     *
     * @param string[] $modulePaths
     *
     * @return string[]
     */
    private function getExcludedModulePaths(array $modulePaths)
    {
        $modulesByBasePath = [];
        foreach ($modulePaths as $modulePath) {
            $moduleDir = $this->file->getPathInfo($modulePath)['basename'];
            $vendorPath = $this->fileDriver->getParentDirectory($modulePath);
            $vendorDir = $this->file->getPathInfo($vendorPath)['basename'];
            $basePath = $this->fileDriver->getParentDirectory($vendorPath);
            $modulesByBasePath[$basePath][$vendorDir][] = $moduleDir;
        }

        $basePathsRegExps = [];
        foreach ($modulesByBasePath as $basePath => $vendorPaths) {
            $vendorPathsRegExps = [];
            foreach ($vendorPaths as $vendorDir => $vendorModules) {
                $vendorPathsRegExps[] = $vendorDir
                    . '/(?:' . join('|', $vendorModules) . ')';
            }

            $basePathsRegExps[] = preg_quote($basePath, '#')
                . '/(?:' . join('|', $vendorPathsRegExps) . ')';
        }

        $excludedModulePaths = [
            '#^(?:' . join('|', $basePathsRegExps) . ')/Test#',
            '#^(?:' . join('|', $basePathsRegExps) . ')/tests#',
        ];
        return $excludedModulePaths;
    }

    /**
     * Build list of library path regexps which should be excluded from compilation
     *
     * @param string[] $libraryPaths
     *
     * @return string[]
     */
    private function getExcludedLibraryPaths(array $libraryPaths)
    {
        $libraryPaths = array_map(
            function ($libraryPath) {
                return preg_quote($libraryPath, '#');
            },
            $libraryPaths
        );

        $excludedLibraryPaths = [
            '#^(?:' . join('|', $libraryPaths) . ')/([\\w]+/)?Test#',
            '#^(?:' . join('|', $libraryPaths) . ')/([\\w]+/)?tests#',
        ];
        return $excludedLibraryPaths;
    }

    /**
     * Get excluded setup application paths
     *
     * @param string $setupPath
     *
     * @return string[]
     */
    private function getExcludedSetupPaths($setupPath)
    {
        return [
            '#^(?:' . preg_quote($setupPath, '#') . ')(/[\\w]+)*/Test#'
        ];
    }

    /**
     * Delete directories by their code from "var" directory
     *
     * @param array $directoryCodeList
     *
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
     *
     * @return void
     */
    private function configureObjectManager(OutputInterface $output)
    {
        $this->objectManager->configure(
            [
                'preferences' => [ConfigWriterInterface::class => ObjectManager\ConfigWriter\Filesystem::class,
                ], ModificationChain::class => [
                    'arguments' => [
                        'modificationsList' => [
                            'BackslashTrim' => [
                                'instance' => BackslashTrim::class
                            ],
                            'PreferencesResolving' => [
                                'instance' => PreferencesResolving::class
                            ],
                            'InterceptorSubstitution' => [
                                'instance' => InterceptorSubstitution::class
                            ],
                            'InterceptionPreferencesResolving' => [
                                'instance' => PreferencesResolving::class
                            ],
                        ]
                    ]
                ], PluginList::class => [
                    'arguments' => [
                        'cache' => [
                            'instance' => CompiledConfig::class
                        ]
                    ]
                ], ClassesScanner::class => [
                    'arguments' => [
                        'excludePatterns' => $this->excludedPathsList
                    ]
                ], Console::class => [
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
     *
     * @return array
     */
    private function getOperationsConfiguration(
        array $compiledPathsList
    ) {
        $excludePatterns = array_merge([], ...array_values($this->excludedPathsList));

        return [
            OperationFactory::PROXY_GENERATOR => [],
            OperationFactory::REPOSITORY_GENERATOR => [
                'paths' => $compiledPathsList['application'],
            ],
            OperationFactory::DATA_ATTRIBUTES_GENERATOR => [],
            OperationFactory::APPLICATION_CODE_GENERATOR => [
                'paths' => [
                    $compiledPathsList['application'],
                    $compiledPathsList['library'],
                    $compiledPathsList['setup'],
                    $compiledPathsList['generated_helpers'],
                ],
                'filePatterns' => ['php' => '/\.php$/'],
                'excludePatterns' => $excludePatterns,
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
                $compiledPathsList['setup'],
                $compiledPathsList['generated_helpers'],
            ],
            OperationFactory::INTERCEPTION_CACHE => [
                $compiledPathsList['application'],
                $compiledPathsList['library'],
                $compiledPathsList['generated_helpers'],
            ],
            OperationFactory::APPLICATION_ACTION_LIST_GENERATOR => [],
            OperationFactory::PLUGIN_LIST_GENERATOR => [],
        ];
    }
}
