<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Sales\Helper;

/**
 * Sales module base helper
 * @since 2.0.0
 */
class Reorder extends \Magento\Framework\App\Helper\AbstractHelper
{
    const XML_PATH_SALES_REORDER_ALLOW = 'sales/reorder/allow';

    /**
     * @var \Magento\Customer\Model\Session
     * @since 2.0.0
     */
    protected $customerSession;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     * @since 2.0.0
     */
    protected $orderRepository;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
    ) {
        $this->orderRepository = $orderRepository;
        $this->customerSession = $customerSession;
        parent::__construct(
            $context
        );
    }

    /**
     * @return bool
     * @since 2.0.0
     */
    public function isAllow()
    {
        return $this->isAllowed();
    }

    /**
     * Check if reorder is allowed for given store
     *
     * @param \Magento\Store\Model\Store|int|null $store
     * @return bool
     * @since 2.0.0
     */
    public function isAllowed($store = null)
    {
        if ($this->scopeConfig->getValue(self::XML_PATH_SALES_REORDER_ALLOW, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store)) {
            return true;
        }
        return false;
    }

    /**
     * Check is it possible to reorder
     *
     * @param int $orderId
     * @return bool
     * @since 2.0.0
     */
    public function canReorder($orderId)
    {
        $order = $this->orderRepository->get($orderId);
        if (!$this->isAllowed($order->getStore())) {
            return false;
        }
        if ($this->customerSession->isLoggedIn()) {
            return $order->canReorder();
        } else {
            return true;
        }
    }
}
