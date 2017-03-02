<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Api\Data;

/**
 * Validation rule interface.
 * @api
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
     */
    public function getName();

    /**
     * Set validation rule name
     *
     * @param string $name
     * @return $this
     */
    public function setName($name);

    /**
     * Get validation rule value
     *
     * @return string
     */
    public function getValue();

    /**
     * Set validation rule value
     *
     * @param string $value
     * @return $this
     */
    public function setValue($value);
}
