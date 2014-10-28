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
namespace Magento\Sales\Service\V1\Data;

/**
 * Class OrderMapper
 */
class OrderMapper
{
    /**
     * @var OrderBuilder
     */
    protected $orderBuilder;

    /**
     * @var OrderItemMapper
     */
    protected $orderItemMapper;

    /**
     * @var OrderPaymentMapper
     */
    protected $orderPaymentMapper;

    /**
     * @var OrderAddressMapper
     */
    protected $orderAddressMapper;

    /**
     * @param OrderBuilder $orderBuilder
     * @param OrderItemMapper $orderItemMapper
     * @param OrderPaymentMapper $orderPaymentMapper
     * @param OrderAddressMapper $orderAddressMapper
     */
    public function __construct(
        OrderBuilder $orderBuilder,
        OrderItemMapper $orderItemMapper,
        OrderPaymentMapper $orderPaymentMapper,
        OrderAddressMapper $orderAddressMapper
    ) {
        $this->orderBuilder = $orderBuilder;
        $this->orderItemMapper = $orderItemMapper;
        $this->orderPaymentMapper = $orderPaymentMapper;
        $this->orderAddressMapper = $orderAddressMapper;
    }

    /**
     * Returns array of items
     *
     * @param \Magento\Sales\Model\Order $object
     * @return OrderItem[]
     */
    protected function getItems(\Magento\Sales\Model\Order $object)
    {
        $items = [];
        foreach ($object->getItemsCollection() as $item) {
            $items[] = $this->orderItemMapper->extractDto($item);
        }
        return $items;
    }

    /**
     * Returns array of payments
     *
     * @param \Magento\Sales\Model\Order $object
     * @return OrderPayment[]
     */
    protected function getPayments(\Magento\Sales\Model\Order $object)
    {
        $payments = [];
        foreach ($object->getPaymentsCollection() as $payment) {
            $payments[] = $this->orderPaymentMapper->extractDto($payment);
        }
        return $payments;
    }

    /**
     * Return billing address
     *
     * @param \Magento\Sales\Model\Order $object
     * @return OrderAddress|null
     */
    protected function getBillingAddress(\Magento\Sales\Model\Order $object)
    {
        $billingAddress = null;
        if ($object->getBillingAddress()) {
            $billingAddress = $this->orderAddressMapper->extractDto($object->getBillingAddress());
        }
        return $billingAddress;
    }

    /**
     * Returns shipping address
     *
     * @param \Magento\Sales\Model\Order $object
     * @return OrderAddress|null
     */
    protected function getShippingAddress(\Magento\Sales\Model\Order $object)
    {
        $shippingAddress = null;
        if ($object->getShippingAddress()) {
            $shippingAddress = $this->orderAddressMapper->extractDto($object->getShippingAddress());
        }
        return $shippingAddress;
    }

    /**
     * @param \Magento\Sales\Model\Order $object
     * @return \Magento\Sales\Service\V1\Data\Order
     */
    public function extractDto(\Magento\Sales\Model\Order $object)
    {
        $this->orderBuilder->populateWithArray($object->getData());
        $this->orderBuilder->setItems($this->getItems($object));
        $this->orderBuilder->setPayments($this->getPayments($object));
        $this->orderBuilder->setBillingAddress($this->getBillingAddress($object));
        $this->orderBuilder->setShippingAddress($this->getShippingAddress($object));
        return $this->orderBuilder->create();
    }
}
