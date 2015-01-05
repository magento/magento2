<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Tax\Api\Data;

interface TaxRateTitleInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**#@+
     *
     * Tax rate field key.
     */
    const KEY_STORE_ID = 'store_id';

    const KEY_VALUE_ID = 'value';
    /**#@-*/

    /**
     * Get store id
     *
     * @return string
     */
    public function getStoreId();

    /**
     * Get title value
     *
     * @return string
     */
    public function getValue();
}
