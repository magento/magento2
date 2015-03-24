<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tools\Di\App;

use Magento\Framework\App;
use Magento\Framework\App\Console\Response;
use Magento\Framework\ObjectManagerInterface;

/**
 * Class Compiler
 * @package Magento\Tools\Di\App
 *
 */
class Compiler implements \Magento\Framework\AppInterface
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var Task\Manager
     */
    private $taskManager;

    /**
     * @var Response
     */
    private $response;

    /**
     * @var array
     */
    private $compiledPathsList = [];

    /**
     * @var array
     */
    private $excludedPathsList = [];

    /**
     * @param Task\Manager $taskManager
     * @param ObjectManagerInterface $objectManager
     * @param Response $response
     * @param array $compiledPathsList
     * @param array $excludedPathsList
     */
    public function __construct(
        Task\Manager $taskManager,
        ObjectManagerInterface $objectManager,
        Response $response,
        $compiledPathsList = [],
        $excludedPathsList = []
    ) {
        $this->taskManager = $taskManager;
        $this->objectManager = $objectManager;
        $this->response = $response;

        if (empty($compiledPathsList)) {
            $compiledPathsList = [
                'application' => BP . '/'  . 'app/code',
                'library' => BP . '/'  . 'lib/internal/Magento/Framework',
                'generated_helpers' => BP . '/'  . 'var/generation'
            ];
        }
        $this->compiledPathsList = $compiledPathsList;

        if (empty($excludedPathsList)) {
            $excludedPathsList = [
                'application' => '#^' . BP . '/app/code/[\\w]+/[\\w]+/Test#',
                'framework' => '#^' . BP . '/lib/internal/[\\w]+/[\\w]+/([\\w]+/)?Test#'
            ];
        }
        $this->excludedPathsList = $excludedPathsList;
    }

    /**
     * Launch application
     *
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function launch()
    {
        $this->objectManager->configure(
            [
                'preferences' =>
                [
                    'Magento\Tools\Di\Compiler\Config\WriterInterface' =>
                        'Magento\Tools\Di\Compiler\Config\Writer\Filesystem',
                    'Magento\Tools\Di\Compiler\Log\Writer\WriterInterface' =>
                        'Magento\Tools\Di\Compiler\Log\Writer\Console'
                ],
                'Magento\Tools\Di\Compiler\Config\ModificationChain' => [
                    'arguments' => [
                        'modificationsList' => [
                            'BackslashTrim' =>
                                ['instance' => 'Magento\Tools\Di\Compiler\Config\Chain\BackslashTrim'],
                            'PreferencesResolving' =>
                                ['instance' => 'Magento\Tools\Di\Compiler\Config\Chain\PreferencesResolving'],
                            'InterceptorSubstitution' =>
                                ['instance' => 'Magento\Tools\Di\Compiler\Config\Chain\InterceptorSubstitution'],
                            'InterceptionPreferencesResolving' =>
                                ['instance' => 'Magento\Tools\Di\Compiler\Config\Chain\PreferencesResolving'],
                            'ArgumentsSerialization' =>
                                ['instance' => 'Magento\Tools\Di\Compiler\Config\Chain\ArgumentsSerialization'],
                        ]
                    ]
                ],
                'Magento\Tools\Di\Code\Generator\PluginList' => [
                    'arguments' => [
                        'cache' => [
                            'instance' => 'Magento\Framework\App\Interception\Cache\CompiledConfig'
                        ]
                    ]
                ],
                'Magento\Tools\Di\Code\Reader\ClassesScanner' => [
                    'arguments' => [
                        'excludePatterns' => $this->excludedPathsList
                    ]
                ]
            ]
        );

        $operations = [
            Task\OperationFactory::REPOSITORY_GENERATOR => [
                'path' => $this->compiledPathsList['application'],
                'filePatterns' => ['di' => '/\/etc\/([a-zA-Z_]*\/di|di)\.xml$/']
            ],
            Task\OperationFactory::APPLICATION_CODE_GENERATOR => [
                $this->compiledPathsList['application'],
                $this->compiledPathsList['library'],
                $this->compiledPathsList['generated_helpers'],
            ],
            Task\OperationFactory::INTERCEPTION =>
                [
                    'intercepted_paths' => [
                        $this->compiledPathsList['application'],
                        $this->compiledPathsList['library'],
                        $this->compiledPathsList['generated_helpers'],
                    ],
                    'path_to_store' => $this->compiledPathsList['generated_helpers'],
                ],
            Task\OperationFactory::AREA_CONFIG_GENERATOR => [
                $this->compiledPathsList['application'],
                $this->compiledPathsList['library'],
                $this->compiledPathsList['generated_helpers'],
            ],
            Task\OperationFactory::INTERCEPTION_CACHE => [
                $this->compiledPathsList['application'],
                $this->compiledPathsList['library'],
                $this->compiledPathsList['generated_helpers'],
            ]
        ];

        $responseCode = Response::SUCCESS;
        try {
            foreach ($operations as $operationCode => $arguments) {
                $this->taskManager->addOperation(
                    $operationCode,
                    $arguments
                );
            }
            $this->taskManager->process();

        } catch (Task\OperationException $e) {
            $responseCode = Response::ERROR;
            $this->response->setBody($e->getMessage());
        }

        $this->response->setCode($responseCode);
        return $this->response;
    }

    /**
     * Ability to handle exceptions that may have occurred during bootstrap and launch
     *
     * Return values:
     * - true: exception has been handled, no additional action is needed
     * - false: exception has not been handled - pass the control to Bootstrap
     *
     * @param App\Bootstrap $bootstrap
     * @param \Exception $exception
     * @return bool
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function catchException(App\Bootstrap $bootstrap, \Exception $exception)
    {
        return false;
    }
}
