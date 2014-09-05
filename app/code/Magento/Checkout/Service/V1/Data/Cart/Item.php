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

namespace Magento\Checkout\Service\V1\Data\Cart;

/**
 * @codeCoverageIgnore
 */
class Item extends \Magento\Framework\Service\Data\AbstractExtensibleObject
{
    /**#@+
     * Constants defined for keys of array
     */
    const ITEM_ID = 'item_id';

    const SKU = 'sku';

    const QTY = 'qty';

    const NAME = 'name';

    const PRICE = 'price';

    const PRODUCT_TYPE = 'product_type';

    /**
     * @return int|null
     */
    public function getItemId()
    {
        return $this->_get(self::ITEM_ID);
    }

    /**
     * @return string|null
     */
    public function getSku()
    {
        return $this->_get(self::SKU);
    }

    /**
     * @return int
     */
    public function getQty()
    {
        return $this->_get(self::QTY);
    }

    /**
     * @return string|null
     */
    public function getName()
    {
        return $this->_get(self::NAME);
    }

    /**
     * @return float|null
     */
    public function getPrice()
    {
        return $this->_get(self::PRICE);
    }

    /**
     * @return string|null
     */
    public function getProductType()
    {
        return $this->_get(self::PRODUCT_TYPE);
    }
}
