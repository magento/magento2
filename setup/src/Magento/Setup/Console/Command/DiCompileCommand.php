<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Console\Command;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Filesystem\DriverInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Config\ConfigOptionsListConstants;
use Magento\Setup\Model\ObjectManagerProvider;
use Magento\Setup\Module\Di\App\Task\Manager;
use Magento\Setup\Module\Di\App\Task\OperationFactory;
use Magento\Setup\Module\Di\App\Task\OperationException;
use Magento\Setup\Module\Di\App\Task\OperationInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;

/**
 * Command to run compile in single-tenant mode
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DiCompileCommand extends Command
{
    /** Command name */
    const NAME = 'setup:di:compile';

    /** @var DeploymentConfig */
    private $deploymentConfig;

    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var Manager */
    private $taskManager;

    /** @var DirectoryList */
    private $directoryList;

    /** @var Filesystem */
    private $filesystem;

    /** @var array */
    private $excludedPathsList;

    /** @var DriverInterface */
    private $fileDriver;

    /**
     * @var ComponentRegistrar
     */
    private $componentRegistrar;

    /**
     * Constructor
     *
     * @param DeploymentConfig $deploymentConfig
     * @param DirectoryList $directoryList
     * @param Manager $taskManager
     * @param ObjectManagerProvider $objectManagerProvider
     * @param Filesystem $filesystem
     * @param DriverInterface $fileDriver
     * @param \Magento\Framework\Component\ComponentRegistrar $componentRegistrar
     */
    public function __construct(
        DeploymentConfig $deploymentConfig,
        DirectoryList $directoryList,
        Manager $taskManager,
        ObjectManagerProvider $objectManagerProvider,
        Filesystem $filesystem,
        DriverInterface $fileDriver,
        ComponentRegistrar $componentRegistrar
    ) {
        $this->deploymentConfig = $deploymentConfig;
        $this->directoryList    = $directoryList;
        $this->objectManager    = $objectManagerProvider->get();
        $this->taskManager      = $taskManager;
        $this->filesystem       = $filesystem;
        $this->fileDriver       = $fileDriver;
        $this->componentRegistrar  = $componentRegistrar;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $errors = $this->checkEnvironment();
        if ($errors) {
            foreach ($errors as $line) {
                $output->writeln($line);
            }
            // we must have an exit code higher than zero to indicate something was wrong
            return \Magento\Framework\Console\Cli::RETURN_FAILURE;
        }

        $modulePaths = $this->componentRegistrar->getPaths(ComponentRegistrar::MODULE);
        $libraryPaths = $this->componentRegistrar->getPaths(ComponentRegistrar::LIBRARY);
        $generationPath = $this->directoryList->getPath(DirectoryList::GENERATION);

        $this->objectManager->get('Magento\Framework\App\Cache')->clean();
        $compiledPathsList = [
            'application' => $modulePaths,
            'library' => $libraryPaths,
            'generated_helpers' => $generationPath
        ];
        $this->excludedPathsList = [
            'application' => $this->getExcludedModulePaths($modulePaths),
            'framework' => $this->getExcludedLibraryPaths($libraryPaths),
        ];
        $this->configureObjectManager($output);

        $operations = $this->getOperationsConfiguration($compiledPathsList);

        try {
            $this->cleanupFilesystem(
                [
                    DirectoryList::CACHE,
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
            // we must have an exit code higher than zero to indicate something was wrong
            return \Magento\Framework\Console\Cli::RETURN_FAILURE;
        }
    }

    /**
     * Build list of module path regexps which should be excluded from compilation
     *
     * @param string[] $modulePaths
     * @return string[]
     */
    private function getExcludedModulePaths(array $modulePaths)
    {
        $modulesByBasePath = [];
        foreach ($modulePaths as $modulePath) {
            $moduleDir = basename($modulePath);
            $vendorPath = dirname($modulePath);
            $vendorDir = basename($vendorPath);
            $basePath = dirname($vendorPath);
            $modulesByBasePath[$basePath][$vendorDir][] = $moduleDir;
        }

        $basePathsRegExps = [];
        foreach ($modulesByBasePath as $basePath => $vendorPaths) {
            $vendorPathsRegExps = [];
            foreach ($vendorPaths as $vendorDir => $vendorModules) {
                $vendorPathsRegExps[] = $vendorDir
                    . '/(?:' . join('|', $vendorModules) . ')';
            }
            $basePathsRegExps[] = $basePath
                . '/(?:' . join('|', $vendorPathsRegExps) . ')';
        }

        $excludedModulePaths = [
            '#^(?:' . join('|', $basePathsRegExps) . ')/Test#',
        ];
        return $excludedModulePaths;
    }

    /**
     * Build list of library path regexps which should be excluded from compilation
     *
     * @param string[] $libraryPaths
     * @return string[]
     */
    private function getExcludedLibraryPaths(array $libraryPaths)
    {
        $excludedLibraryPaths = [
            '#^(?:' . join('|', $libraryPaths) . ')/([\\w]+/)?Test#',
        ];
        return $excludedLibraryPaths;
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
     * @return array
     */
    private function getOperationsConfiguration(
        array $compiledPathsList
    ) {
        $excludePatterns = [];
        foreach ($this->excludedPathsList as $excludedPaths) {
            $excludePatterns = array_merge($excludedPaths, $excludePatterns);
        }

        $operations = [
            OperationFactory::PROXY_GENERATOR => [],
            OperationFactory::REPOSITORY_GENERATOR => [
                'paths' => $compiledPathsList['application'],
            ],
            OperationFactory::DATA_ATTRIBUTES_GENERATOR => [],
            OperationFactory::APPLICATION_CODE_GENERATOR => [
                'paths' => [
                    $compiledPathsList['application'],
                    $compiledPathsList['library'],
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
}
