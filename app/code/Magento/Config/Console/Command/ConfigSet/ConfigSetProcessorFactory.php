<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Config\Console\Command\ConfigSet;

use Magento\Config\Console\Command\ConfigSetCommand;
use Magento\Framework\Exception\ConfigurationMismatchException;
use Magento\Framework\ObjectManagerInterface;

/**
 * Creates different implementations of config:set processors of type ConfigSetProcessorInterface.
 *
 * @see ConfigSetProcessorInterface
 * @see ConfigSetCommand
 *
 * @api
 * @since 101.0.0
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
    
    /**
     * @deprecated
     * @see TYPE_LOCK_ENV or TYPE_LOCK_CONFIG
     */
    const TYPE_LOCK = 'lock';
    const TYPE_LOCK_ENV = 'lock-env';
    const TYPE_LOCK_CONFIG = 'lock-config';
    /**#@-*/

    /**#@-*/
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
     * @return ConfigSetProcessorInterface New processor instance
     * @throws ConfigurationMismatchException If processor type is not exists in processors array
     * or declared class has wrong implementation
     * @since 101.0.0
     */
    public function create($processorName)
    {
        if (!isset($this->processors[$processorName])) {
            throw new ConfigurationMismatchException(
                __('The class for "%1" type wasn\'t declared. Enter the class and try again.', $processorName)
            );
        }

        $object = $this->objectManager->create($this->processors[$processorName]);

        if (!$object instanceof ConfigSetProcessorInterface) {
            throw new ConfigurationMismatchException(
                __('%1 should implement %2', get_class($object), ConfigSetProcessorInterface::class)
            );
        }

        return $object;
    }
}
