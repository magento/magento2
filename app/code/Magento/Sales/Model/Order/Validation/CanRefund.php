<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order\Validation;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\ValidatorInterface;

class CanRefund implements ValidatorInterface
{
    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * CanRefund constructor.
     *
     * @param PriceCurrencyInterface $priceCurrency
     * @param ScopeConfigInterface|null $scopeConfig
     */
    public function __construct(
        PriceCurrencyInterface $priceCurrency,
        ?ScopeConfigInterface $scopeConfig = null
    ) {
        $this->priceCurrency = $priceCurrency;
        $this->scopeConfig = $scopeConfig ?? ObjectManager::getInstance()->get(ScopeConfigInterface::class);
    }

    /**
     * @inheritdoc
     */
    public function validate($entity)
    {
        $messages = [];
        if ($entity->getState() === Order::STATE_PAYMENT_REVIEW ||
            $entity->getState() === Order::STATE_HOLDED ||
            $entity->getState() === Order::STATE_CANCELED ||
            $entity->getState() === Order::STATE_CLOSED
        ) {
            $messages[] = __(
                'A creditmemo can not be created when an order has a status of %1',
                $entity->getStatus()
            );
        } elseif (!$this->isTotalPaidEnoughForRefund($entity)) {
            $messages[] = __('The order does not allow a creditmemo to be created.');
        }

        return $messages;
    }

    /**
     * We can have problem with float in php (on some server $a=762.73;$b=762.73; $a-$b!=0)
     * for this we have additional diapason for 0
     * TotalPaid - contains amount, that were not rounded.
     *
     * @param OrderInterface $order
     * @return bool
     */
    private function isTotalPaidEnoughForRefund(OrderInterface $order)
    {
        $isAllowedZeroGrandTotal = $this->scopeConfig->getValue(
            'sales/zerograndtotal_creditmemo/allow_zero_grandtotal',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        return !abs($this->priceCurrency->round($order->getTotalPaid()) - $order->getTotalRefunded()) < .0001 ||
            $order->getTotalPaid() == 0 &&
            $isAllowedZeroGrandTotal;
    }
}
