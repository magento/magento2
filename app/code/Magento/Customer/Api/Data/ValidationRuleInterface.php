<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Api\Data;

/**
 * Validation rule interface.
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
     * @api
     * @return string
     */
    public function getName();

    /**
     * Set validation rule name
     *
     * @api
     * @param string $name
     * @return $this
     */
    public function setName($name);

    /**
     * Get validation rule value
     *
     * @api
     * @return string
     */
    public function getValue();

    /**
     * Set validation rule value
     *
     * @api
     * @param string $value
     * @return $this
     */
    public function setValue($value);
}
