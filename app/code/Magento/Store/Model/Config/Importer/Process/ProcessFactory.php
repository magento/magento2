<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Model\Config\Importer\Process;

use Magento\Framework\Exception\ConfigurationMismatchException;
use Magento\Framework\ObjectManagerInterface;

class ProcessFactory
{
    /**#@+
     * Constants for processors.
     */
    const TYPE_CREATE = 'create';
    const TYPE_DELETE = 'delete';
    /**#@-*/

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * List of class names that implement config:set processors
     *
     * @var array
     * @see ConfigSetProcessorInterface
     */
    private $processors;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param array $processors
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        array $processors = []
    ) {
        $this->objectManager = $objectManager;
        $this->processors = $processors;
    }

    /**
     * Creates an instance of specified processor.
     *
     * @param string $processorName The name of processor
     * @return ProcessInterface New processor instance
     * @throws ConfigurationMismatchException If processor type is not exists in processors array
     * or declared class has wrong implementation
     */
    public function create($processorName)
    {
        if (!isset($this->processors[$processorName])) {
            throw new ConfigurationMismatchException(__('Class for type "%1" was not declared', $processorName));
        }

        $object = $this->objectManager->create($this->processors[$processorName]);

        if (!$object instanceof ProcessInterface) {
            throw new ConfigurationMismatchException(
                __('%1 should implement %2', get_class($object), ProcessInterface::class)
            );
        }

        return $object;
    }
}
