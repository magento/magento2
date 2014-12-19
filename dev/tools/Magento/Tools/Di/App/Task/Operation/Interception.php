<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Tools\Di\App\Task\Operation;

use Magento\Tools\Di\App\Task\OperationInterface;
use Magento\Tools\Di\Code\Generator\InterceptionConfigurationBuilder;
use Magento\Framework\Interception\Code\Generator\Interceptor;

class Interception implements OperationInterface
{
    /**
     * @var InterceptionConfigurationBuilder
     */
    private $interceptionConfigurationBuilder;

    /**
     * @var string
     */
    private $data = '';

    /**
     * @param InterceptionConfigurationBuilder $interceptionConfigurationBuilder
     * @param string $data
     */
    public function __construct(
        InterceptionConfigurationBuilder $interceptionConfigurationBuilder,
        $data = ''
    ) {
        $this->interceptionConfigurationBuilder = $interceptionConfigurationBuilder;
        $this->data = $data;
    }

    /**
     * {@inheritdoc}
     */
    public function doOperation()
    {
        if (empty($this->data)) {
            return;
        }

        $generatorIo = new \Magento\Framework\Code\Generator\Io(
            new \Magento\Framework\Filesystem\Driver\File(),
            $this->data
        );
        $generator = new \Magento\Tools\Di\Code\Generator(
            $generatorIo,
            [
                Interceptor::ENTITY_TYPE => 'Magento\Tools\Di\Code\Generator\Interceptor',
            ]
        );
        $configuration = $this->interceptionConfigurationBuilder->getInterceptionConfiguration(get_declared_classes());
        $generator->generateList($configuration);
    }

}
