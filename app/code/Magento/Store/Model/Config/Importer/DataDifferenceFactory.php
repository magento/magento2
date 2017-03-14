<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Model\Config\Importer;

use Magento\Framework\Exception\ConfigurationMismatchException;
use Magento\Framework\ObjectManagerInterface;

/**
 * Factory class for data difference calculators.
 *
 * @see DataDifferenceInterface
 */
class DataDifferenceFactory
{
    /**
     * The Object Manager.
     *
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * List of class names that implement data difference.
     *
     * @var array
     * @see DataDifferenceInterface
     */
    private $calculators;

    /**
     * @param ObjectManagerInterface $objectManager The Object Manager
     * @param array $calculators List of class names that implement data difference
     */
    public function __construct(ObjectManagerInterface $objectManager, array $calculators = [])
    {
        $this->objectManager = $objectManager;
        $this->calculators = $calculators;
    }

    /**
     * Creates a specific calculator for data difference.
     *
     * @param string $type Name of calculator
     * @return DataDifferenceInterface A calculator for data difference
     * @throws ConfigurationMismatchException If object type is not exists in instances array
     * or declared class has wrong implementation
     */
    public function create($type)
    {
        if (!isset($this->calculators[$type])) {
            throw new ConfigurationMismatchException(__('Class for type "%1" was not declared', $type));
        }

        $object = $this->objectManager->create($this->calculators[$type]);

        if (!$object instanceof DataDifferenceInterface) {
            throw new ConfigurationMismatchException(
                __('%1 should implement %2', get_class($object), DataDifferenceInterface::class)
            );
        }

        return $object;
    }
}
