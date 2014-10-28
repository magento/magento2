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
namespace Magento\Sales\Model\Observer\Frontend\Quote;

use Magento\Customer\Helper\Address as CustomerAddress;

/**
 * Class AddVatRequestParamsOrderComment
 */
class AddVatRequestParamsOrderComment
{
    /**
     * Customer address
     *
     * @var CustomerAddress
     */
    protected $customerAddressHelper;

    /**
     * @param CustomerAddress $customerAddressHelper
     */
    public function __construct(CustomerAddress $customerAddressHelper)
    {
        $this->customerAddressHelper = $customerAddressHelper;
    }

    /**
     * Add VAT validation request date and identifier to order comments
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var $orderInstance \Magento\Sales\Model\Order */
        $orderInstance = $observer->getOrder();
        /** @var $orderAddress \Magento\Sales\Model\Order\Address */
        $orderAddress = $this->_getVatRequiredSalesAddress($orderInstance);
        if (!$orderAddress instanceof \Magento\Sales\Model\Order\Address) {
            return;
        }

        $vatRequestId = $orderAddress->getVatRequestId();
        $vatRequestDate = $orderAddress->getVatRequestDate();
        if (is_string($vatRequestId)
            && !empty($vatRequestId)
            && is_string($vatRequestDate)
            && !empty($vatRequestDate)
        ) {
            $orderHistoryComment = __('VAT Request Identifier')
                . ': ' . $vatRequestId . '<br />'
                . __('VAT Request Date') . ': ' . $vatRequestDate;
            $orderInstance->addStatusHistoryComment($orderHistoryComment, false);
        }
    }

    /**
     * Retrieve sales address (order or quote) on which tax calculation must be based
     *
     * @param \Magento\Sales\Model\Order $order
     * @param \Magento\Store\Model\Store|string|int|null $store
     * @return \Magento\Sales\Model\Order\Address|null
     */
    protected function _getVatRequiredSalesAddress($order, $store = null)
    {
        $configAddressType = $this->customerAddressHelper->getTaxCalculationAddressType($store);
        $requiredAddress = null;
        switch ($configAddressType) {
            case \Magento\Customer\Model\Address\AbstractAddress::TYPE_SHIPPING:
                $requiredAddress = $order->getShippingAddress();
                break;
            default:
                $requiredAddress = $order->getBillingAddress();
                break;
        }
        return $requiredAddress;
    }
}
