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
namespace Magento\Bundle\Service\V1\Data\Product;

use \Magento\Framework\Service\Data\AbstractExtensibleObject;

/**
 * @codeCoverageIgnore
 */
class Link extends AbstractExtensibleObject
{
    const SKU = 'sku';

    const OPTION_ID = 'option_id';

    const QTY = 'qty';

    const CAN_CHANGE_QUANTITY = 'can_change_qty';

    const POSITION = 'position';

    const DEFINED = 'defined';

    const IS_DEFAULT = 'default';

    const PRICE = 'price';

    const PRICE_TYPE = 'price_type';

    /**
     * @return string|null
     */
    public function getSku()
    {
        return $this->_get(self::SKU);
    }

    /**
     * @return int|null
     */
    public function getOptionId()
    {
        return $this->_get(self::OPTION_ID);
    }

    /**
     * @return float|null
     */
    public function getQty()
    {
        return $this->_get(self::QTY);
    }

    /**
     * @return int|null
     */
    public function getPosition()
    {
        return $this->_get(self::POSITION);
    }

    /**
     * @return bool|null
     */
    public function isDefined()
    {
        return (bool)$this->_get(self::DEFINED);
    }

    /**
     * @return bool
     */
    public function isDefault()
    {
        return (bool)$this->_get(self::IS_DEFAULT);
    }

    /**
     * @return float
     */
    public function getPrice()
    {
        return $this->_get(self::PRICE);
    }

    /**
     * @return int
     */
    public function getPriceType()
    {
        return $this->_get(self::PRICE_TYPE);
    }

    /**
     * Get whether quantity could be changed
     *
     * @return int|null
     */
    public function getCanChangeQuantity()
    {
        return $this->_get(self::CAN_CHANGE_QUANTITY);
    }
}
