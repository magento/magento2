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

use Magento\Framework\Service\Data\AbstractExtensibleObject;

/**
 * Class TaxRule
 */
class TaxRule extends AbstractExtensibleObject
{
    /**#@+
     * Constants defined for keys of array, makes typos less likely
     */
    const ID = 'id';

    const CODE = 'code';

    const CUSTOMER_TAX_CLASS_IDS = 'customer_tax_class_ids';

    const PRODUCT_TAX_CLASS_IDS = 'product_tax_class_ids';

    const TAX_RATE_IDS = 'tax_rate_ids';

    const PRIORITY = 'priority';

    const SORT_ORDER = 'sort_order';

    const CALCULATE_SUBTOTAL = 'calculate_subtotal';
    /**#@-*/

    /**
     * Get id
     *
     * @return int|null
     */
    public function getId()
    {
        return $this->_get(self::ID);
    }

    /**
     * Get tax rule code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->_get(self::CODE);
    }

    /**
     * Get customer tax class id
     *
     * @return int[]
     */
    public function getCustomerTaxClassIds()
    {
        return $this->_get(self::CUSTOMER_TAX_CLASS_IDS);
    }

    /**
     * Get product tax class id
     *
     * @return int[]
     */
    public function getProductTaxClassIds()
    {
        return $this->_get(self::PRODUCT_TAX_CLASS_IDS);
    }

    /**
     * Get tax rate ids
     *
     * @return int[]
     */
    public function getTaxRateIds()
    {
        return $this->_get(self::TAX_RATE_IDS);
    }

    /**
     * Get priority
     *
     * @return int
     */
    public function getPriority()
    {
        return $this->_get(self::PRIORITY);
    }

    /**
     * Get sort order.
     *
     * @return int
     */
    public function getSortOrder()
    {
        return $this->_get(self::SORT_ORDER);
    }

    /**
     * Get calculate subtotal.
     *
     * @return bool|null
     */
    public function getCalculateSubtotal()
    {
        return $this->_get(self::CALCULATE_SUBTOTAL);
    }
}
