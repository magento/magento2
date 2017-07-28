<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Model\Config\Importer\Processor;

use Magento\Framework\Exception\ConfigurationMismatchException;
use Magento\Framework\ObjectManagerInterface;

/**
 * The factory for creating importing processors.
 *
 * @see ProcessorInterface
 * @since 2.2.0
 */
class ProcessorFactory
{
    /**#@+
     * Constants for processor types.
     */
    const TYPE_CREATE = 'create';
    const TYPE_DELETE = 'delete';
    const TYPE_UPDATE = 'update';
    /**#@-*/

    /**
     * The Object Manager.
     *
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * List of class names that implement processes.
     *
     * @var array
     * @see ProcessorInterface
     * @since 2.2.0
     */
    private $processors;

    /**
     * @param ObjectManagerInterface $objectManager The Object Manager
     * @param array $processors List of class names that implement processes
     * @since 2.2.0
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
     * @return ProcessorInterface New processor instance
     * @throws ConfigurationMismatchException If processor type is not exists in processors array
     * or declared class has wrong implementation
     * @since 2.2.0
     */
    public function create($processorName)
    {
        if (!isset($this->processors[$processorName])) {
            throw new ConfigurationMismatchException(__('Class for type "%1" was not declared', $processorName));
        }

        $object = $this->objectManager->create($this->processors[$processorName]);

        if (!$object instanceof ProcessorInterface) {
            throw new ConfigurationMismatchException(
                __('%1 should implement %2', get_class($object), ProcessorInterface::class)
            );
        }

        return $object;
    }
}
