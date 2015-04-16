<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Console\Command;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Console\ObjectManagerProvider;
use Magento\Framework\App\DeploymentConfig;
use Magento\Setup\Module\Di\App\Task\Manager;
use Magento\Setup\Module\Di\App\Task\OperationFactory;
use Magento\Setup\Module\Di\App\Task\OperationException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DiCompileCommand extends AbstractSetupCommand
{
    /**
     * @var DeploymentConfig
     */
    private $deploymentConfig;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var Manager
     */
    private $taskManager;

    /**
     * Constructor
     *
     * @param DeploymentConfig $deploymentConfig
     * @param Manager $taskManager
     * @param ObjectManagerProvider $objectManagerProvider
     */
    public function __construct(
        DeploymentConfig $deploymentConfig,
        Manager $taskManager,
        ObjectManagerProvider $objectManagerProvider
    ) {
        $this->deploymentConfig = $deploymentConfig;
        $this->objectManager = $objectManagerProvider->get();
        $this->taskManager = $taskManager;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('setup:di:compile')
            ->setDescription('Compiles for single tenant');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$this->deploymentConfig->isAvailable()) {
            $output->writeln('Application is not installed yet.');
            return;
        }
        $compiledPathsList = [
            'application' => BP . '/'  . 'app/code',
            'library' => BP . '/'  . 'lib/internal/Magento/Framework',
            'generated_helpers' => BP . '/'  . 'var/generation'
        ];
        $excludedPathsList = [
            'application' => '#^' . BP . '/app/code/[\\w]+/[\\w]+/Test#',
            'framework' => '#^' . BP . '/lib/internal/[\\w]+/[\\w]+/([\\w]+/)?Test#'
        ];
        $this->objectManager->configure(
            [
                'preferences' => [
                    'Magento\Setup\Module\Di\Compiler\Config\WriterInterface' =>
                        'Magento\Setup\Module\Di\Compiler\Config\Writer\Filesystem',
                    'Symfony\Component\Console\Output\OutputInterface' =>
                        get_class($output),
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
                        'excludePatterns' => $excludedPathsList
                    ]
                ]
            ]
        );

        $operations = [
            OperationFactory::REPOSITORY_GENERATOR => [
                'path' => $compiledPathsList['application'],
                'filePatterns' => ['di' => '/\/etc\/([a-zA-Z_]*\/di|di)\.xml$/']
            ],
            OperationFactory::APPLICATION_CODE_GENERATOR => [
                $compiledPathsList['application'],
                $compiledPathsList['library'],
                $compiledPathsList['generated_helpers'],
            ],
            OperationFactory::INTERCEPTION =>
                [
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

        try {
            foreach ($operations as $operationCode => $arguments) {
                $this->taskManager->addOperation(
                    $operationCode,
                    $arguments
                );
            }
            $this->taskManager->process();
        } catch (OperationException $e) {
            $output->writeln($e->getMessage());
        }
    }
}
