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
class ItemBuilder extends \Magento\Framework\Service\Data\AbstractObjectBuilder
{
    /**
     * @param string $value
     * @return $this
     */
    public function setSku($value)
    {
        $this->_set(Item::SKU, $value);
        return $this;
    }

    /**
     * @param int $value
     * @return $this
     */
    public function setQty($value)
    {
        $this->_set(Item::QTY, $value);
        return $this;
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setName($value)
    {
        $this->_set(Item::NAME, $value);
        return $this;
    }

    /**
     * @param float $value
     * @return $this
     */
    public function setPrice($value)
    {
        $this->_set(Item::PRICE, $value);
        return $this;
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setType($value)
    {
        $this->_set(Item::TYPE, $value);
        return $this;
    }
}
