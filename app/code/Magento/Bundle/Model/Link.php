<?php
/**
 *
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
namespace Magento\Bundle\Model;

class Link extends \Magento\Framework\Model\AbstractExtensibleModel implements
    \Magento\Bundle\Api\Data\LinkInterface
{
    /**
     * {@inheritdoc}
     */
    public function getSku()
    {
        return $this->getData('sku');
    }

    /**
     * {@inheritdoc}
     */
    public function getOptionId()
    {
        return $this->getData('option_id');
    }

    /**
     * {@inheritdoc}
     */
    public function getQty()
    {
        return $this->getData('qty');
    }

    /**
     * {@inheritdoc}
     */
    public function getPosition()
    {
        return $this->getData('position');
    }

    /**
     * {@inheritdoc}
     */
    public function getIsDefined()
    {
        return $this->getData('is_defined');
    }

    /**
     * {@inheritdoc}
     */
    public function getIsDefault()
    {
        return $this->getData('is_default');
    }

    /**
     * {@inheritdoc}
     */
    public function getPrice()
    {
        return $this->getData('price');
    }

    /**
     * {@inheritdoc}
     */
    public function getPriceType()
    {
        return $this->getData('price_type');
    }

    /**
     * {@inheritdoc}
     */
    public function getCanChangeQuantity()
    {
        return $this->getData('can_change_quantity');
    }
}
