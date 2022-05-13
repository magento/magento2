<?php
/**
 * Object Manager class definition interface
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\ObjectManager;

/**
 * Interface \Magento\Framework\ObjectManager\DefinitionInterface
 *
 * @api
 */
interface DefinitionInterface
{
    /**
     * Get list of method parameters
     *
     * Retrieve an ordered list of constructor parameters.
     * Each value is an array with following entries:
     *
     * array(
     *     0, // string: Parameter name
     *     1, // string|null: Parameter type
     *     2, // bool: whether this param is required
     *     3, // mixed: default value
     * );
     *
     * @param string $className
     * @return array|null
     */
    public function getParameters($className);

    /**
     * Retrieve list of all classes covered with definitions
     *
     * @return array
     */
    public function getClasses();
}
