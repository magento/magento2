<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Console\Command\ConfigSet;

use Magento\Framework\Exception\ConfigurationMismatchException;
use Magento\Framework\ObjectManagerInterface;

/**
 * Creates different implementations of config:set processors.
 */
class ConfigSetProcessorFactory
{
    /**#@+
     * Constants for processors.
     *
     * default - save configuration
     * lock - save and lock configuration
     */
    const TYPE_DEFAULT = 'default';
    const TYPE_LOCK = 'lock';
    /**#@-*/

    /**
     * The Object Manager.
     *
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * List of config:set command processors.
     *
     * @var array
     */
    private $processors;

    /**
     * @param ObjectManagerInterface $objectManager The Object Manager
     * @param array $processors List of processors
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
     * @param string $processor The name of processor
     * @return ConfigSetProcessorInterface New processor instance
     * @throws ConfigurationMismatchException If processor type is not exists in processors array
     */
    public function create($processor)
    {
        if (!isset($this->processors[$processor])) {
            throw new ConfigurationMismatchException(__('Class for type "%1" was not declared', $processor));
        }

        $object = $this->objectManager->create($this->processors[$processor]);

        if (!$object instanceof ConfigSetProcessorInterface) {
            throw new ConfigurationMismatchException(
                __('%1 does not implement %2', get_class($object), ConfigSetProcessorInterface::class)
            );
        }

        return $object;
    }
}
