<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Tools\Di\App\Task\Operation;
use Magento\Tools\Di\App\Task\OperationInterface;

class InterceptionCache implements OperationInterface
{
    /**
     * @var array
     */
    private $data = [];

    private $configInterface;

    public function __construct(
        \Magento\Framework\Interception\Config\Config $configInterface,
        $data = []
    ) {
        $this->data = $data;
        $this->configInterface = $configInterface;
    }

    /**
     * Flushes interception cached configuration and generates a new one
     *
     * @return void
     */
    public function doOperation()
    {
        if (empty($this->data)) {
            return;
        }

        $logWriter = new \Magento\Tools\Di\Compiler\Log\Writer\Quiet();
        $errorWriter = new \Magento\Tools\Di\Compiler\Log\Writer\Console();

        $log = new \Magento\Tools\Di\Compiler\Log\Log($logWriter, $errorWriter);

        $validator = new \Magento\Framework\Code\Validator();
        $validator->add(new \Magento\Framework\Code\Validator\ConstructorIntegrity());
        $validator->add(new \Magento\Framework\Code\Validator\ContextAggregation());

        $directoryCompiler = new \Magento\Tools\Di\Compiler\Directory($log, $validator);
        foreach ($this->data as $path) {
            if (is_readable($path)) {
                $directoryCompiler->compile($path);
            }
        }

        list($definitions, ) = $directoryCompiler->getResult();

        $this->configInterface->initialize(array_keys($definitions));
    }
}
