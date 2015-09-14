<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Quote\Api\Data;

/**
 * Interface ShippingAssignmentInterface
 */
interface ShippingAssignmentInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**
     * @return \Magento\Quote\Api\Data\ShippingInterface
     */
    public function getShipping();

    /**
     * @param ShippingInterface $value
     * @return \Magento\Quote\Api\Data\ShippingInterface
     */
    public function setShipping(\Magento\Quote\Api\Data\ShippingInterface $value);

    /**
     * @return \Magento\Quote\Api\Data\CartItemInterface[]
     */
    public function getItems();

    /**
     * @param \Magento\Quote\Api\Data\CartItemInterface[] $value
     * @return mixed
     */
    public function setItems($value);
}
