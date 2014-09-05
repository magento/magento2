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
namespace Magento\Tax\Service\V1\Data\OrderTaxDetails;

class AppliedTax extends \Magento\Framework\Service\Data\AbstractExtensibleObject
{
    /**#@+
     * Constants defined for keys of array, makes typos less likely
     */
    const KEY_CODE = 'code';

    const KEY_TITLE = 'title';

    const KEY_PERCENT = 'percent';

    const KEY_AMOUNT = 'amount';

    const KEY_BASE_AMOUNT = 'base_amount';
    /**#@-*/

    /**
     * Get code
     *
     * @return string|null
     */
    public function getCode()
    {
        return $this->_get(self::KEY_CODE);
    }

    /**
     * Get Title
     *
     * @return string|null
     */
    public function getTitle()
    {
        return $this->_get(self::KEY_TITLE);
    }

    /**
     * Get Tax Percent
     *
     * @return float|null
     */
    public function getPercent()
    {
        return $this->_get(self::KEY_PERCENT);
    }

    /**
     * Get tax amount
     *
     * @return float
     */
    public function getAmount()
    {
        return $this->_get(self::KEY_AMOUNT);
    }

    /**
     * Get tax amount in base currency
     *
     * @return float
     */
    public function getBaseAmount()
    {
        return $this->_get(self::KEY_BASE_AMOUNT);
    }
}
