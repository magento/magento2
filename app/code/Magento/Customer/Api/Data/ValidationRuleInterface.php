<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Api\Data;

/**
 * Validation rule interface.
 * @api
 * @since 2.0.0
 */
interface ValidationRuleInterface
{
    /**#@+
     * Constants for keys of data array
     */
    const NAME = 'name';
    const VALUE = 'value';
    /**#@-*/

    /**
     * Get validation rule name
     *
     * @return string
     * @since 2.0.0
     */
    public function getName();

    /**
     * Set validation rule name
     *
     * @param string $name
     * @return $this
     * @since 2.0.0
     */
    public function setName($name);

    /**
     * Get validation rule value
     *
     * @return string
     * @since 2.0.0
     */
    public function getValue();

    /**
     * Set validation rule value
     *
     * @param string $value
     * @return $this
     * @since 2.0.0
     */
    public function setValue($value);
}
