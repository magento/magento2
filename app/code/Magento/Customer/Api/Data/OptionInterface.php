<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Api\Data;

/**
 * Option interface.
 */
interface OptionInterface
{
    /**#@+
     * Constants for keys of data array
     */
    const LABEL = 'label';
    const VALUE = 'value';
    const OPTIONS = 'options';
    /**#@-*/

    /**
     * Get option label
     *
     * @api
     * @return string
     */
    public function getLabel();

    /**
     * Set option label
     *
     * @api
     * @param string $label
     * @return $this
     */
    public function setLabel($label);

    /**
     * Get option value
     *
     * @api
     * @return string|null
     */
    public function getValue();

    /**
     * Set option value
     *
     * @api
     * @param string $value
     * @return $this
     */
    public function setValue($value);

    /**
     * Get nested options
     *
     * @api
     * @return \Magento\Customer\Api\Data\OptionInterface[]|null
     */
    public function getOptions();

    /**
     * Set nested options
     *
     * @api
     * @param \Magento\Customer\Api\Data\OptionInterface[] $options
     * @return $this
     */
    public function setOptions(array $options = null);
}
