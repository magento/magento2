<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Tools\Di\App\Task\Operation;

use Magento\Tools\Di\App\Task\OperationInterface;
use Magento\Tools\Di\Compiler\Config;

class Relations implements OperationInterface
{
    /**
     * @var Config\WriterInterface
     */
    private $configWriter;

    /**
     * @var array
     */
    private $data = [];

    /**
     * Code
     */
    const CODE = 'relations';

    /**
     * @param Config\WriterInterface $configWriter
     * @param array $data
     */
    public function __construct(
        Config\WriterInterface $configWriter,
        $data = []
    ) {
        $this->configWriter = $configWriter;
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

        list(, $relations) = $directoryCompiler->getResult();
        $this->configWriter->write(self::CODE, $relations);
    }
}
