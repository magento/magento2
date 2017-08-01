<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Api\Data;

/**
 * Option interface.
 * @api
 * @since 2.0.0
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
     * @return string
     * @since 2.0.0
     */
    public function getLabel();

    /**
     * Set option label
     *
     * @param string $label
     * @return $this
     * @since 2.0.0
     */
    public function setLabel($label);

    /**
     * Get option value
     *
     * @return string|null
     * @since 2.0.0
     */
    public function getValue();

    /**
     * Set option value
     *
     * @param string $value
     * @return $this
     * @since 2.0.0
     */
    public function setValue($value);

    /**
     * Get nested options
     *
     * @return \Magento\Customer\Api\Data\OptionInterface[]|null
     * @since 2.0.0
     */
    public function getOptions();

    /**
     * Set nested options
     *
     * @param \Magento\Customer\Api\Data\OptionInterface[] $options
     * @return $this
     * @since 2.0.0
     */
    public function setOptions(array $options = null);
}
