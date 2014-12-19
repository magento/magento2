<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Tools\Di\App;

use Magento\Framework\App;

/**
 * Class Compiler
 * @package Magento\Tools\Di\App
 *
 */
class Compiler implements \Magento\Framework\AppInterface
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var Task\Manager
     */
    private $taskManager;

    /**
     * @param Task\Manager $taskManager
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        Task\Manager $taskManager,
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->taskManager = $taskManager;
        $this->objectManager = $objectManager;
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
                        'Magento\Tools\Di\Compiler\Config\Writer\Filesystem'
                ]
            ]
        );

        $this->taskManager->addOperation(
            Task\OperationFactory::AREA,
            [BP . '/'  . 'app/code', BP . '/'  . 'lib/internal/Magento/Framework', BP . '/'  . 'var/generation']
        );
        $this->taskManager->addOperation(
            Task\OperationFactory::INTERCEPTION,
            BP . '/var/generation'
        );
        $this->taskManager->addOperation(
            Task\OperationFactory::RELATIONS,
            [BP . '/'  . 'app/code', BP . '/'  . 'lib/internal/Magento/Framework', BP . '/'  . 'var/generation']
        );
        $this->taskManager->addOperation(
            Task\OperationFactory::PLUGINS,
            BP . '/app'
        );
        $this->taskManager->process();

        $response = new \Magento\Framework\App\Console\Response();
        $response->setCode(0);
        return $response;
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
