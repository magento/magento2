<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Tax\Service\V1\Data;

class QuoteDetails extends \Magento\Framework\Service\Data\AbstractExtensibleObject
{
    /**#@+
     * Constants defined for keys of array, makes typos less likely
     */
    const KEY_BILLING_ADDRESS = 'billing_address';

    const KEY_SHIPPING_ADDRESS = 'shipping_address';

    const KEY_CUSTOMER_TAX_CLASS_KEY = 'customer_tax_class_key';

    const KEY_ITEMS = 'items';

    const CUSTOMER_TAX_CLASS_ID = 'customer_tax_class_id';

    const KEY_CUSTOMER_ID = 'customer_id';
    /**#@-*/

    /**
     * Get customer billing address
     *
     * @return \Magento\Customer\Service\V1\Data\Address|null
     */
    public function getBillingAddress()
    {
        return $this->_get(self::KEY_BILLING_ADDRESS);
    }

    /**
     * Get customer shipping address
     *
     * @return \Magento\Customer\Service\V1\Data\Address|null
     */
    public function getShippingAddress()
    {
        return $this->_get(self::KEY_SHIPPING_ADDRESS);
    }

    /**
     * Get customer tax class key
     *
     * @return \Magento\Tax\Service\V1\Data\TaxClassKey|null
     */
    public function getCustomerTaxClassKey()
    {
        return $this->_get(self::KEY_CUSTOMER_TAX_CLASS_KEY);
    }

    /**
     * Get customer id
     *
     * @return int|null
     */
    public function getCustomerId()
    {
        return $this->_get(self::KEY_CUSTOMER_ID);
    }

    /**
     * Get customer data
     *
     * @return \Magento\Tax\Service\V1\Data\QuoteDetails\Item[]|null
     */
    public function getItems()
    {
        return $this->_get(self::KEY_ITEMS);
    }

    /**
     * Get customer tax class id
     *
     * @return int
     */
    public function getCustomerTaxClassId()
    {
        return $this->_get(self::CUSTOMER_TAX_CLASS_ID);
    }
}
