<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
     * @return string
     */
    public function getLabel();

    /**
     * Get option value
     *
     * @return string|null
     */
    public function getValue();

    /**
     * Get nested options
     *
     * @return \Magento\Customer\Api\Data\OptionInterface[]|null
     */
    public function getOptions();
}
