<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Helper;

/**
 * Sales module base helper
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class Reorder extends \Magento\Framework\App\Helper\AbstractHelper
{
    const XML_PATH_SALES_REORDER_ALLOW = 'sales/reorder/allow';

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
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
     * Is reorder allowed ?
     *
     * @return bool
     */
    public function isAllow(): bool
    {
        return $this->isAllowed();
    }

    /**
     * Check if reorder is allowed for given store
     *
     * @param \Magento\Store\Model\Store|int|null $store
     * @return bool
     */
    public function isAllowed($store = null): bool
    {
        if ($this->scopeConfig->getValue(
            self::XML_PATH_SALES_REORDER_ALLOW,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        )) {
            return true;
        }
        return false;
    }

    /**
     * Check is it possible to reorder
     *
     * @param int $orderId
     * @return bool
     */
    public function canReorder($orderId): bool
    {
        $order = $this->orderRepository->get($orderId);
        if (!$this->isAllowed($order->getStore())) {
            return false;
        }
        if ($this->customerSession->isLoggedIn()) {
            return (bool) $order->canReorder();
        } else {
            return true;
        }
    }

    /**
     * Check is it possible to reorder one item.
     *
     * @param \Magento\Sales\Model\Order\Item $item
     * @param \Magento\Catalog\Model\Product $product
     * @return bool
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function canReorderItem($item, $product): bool
    {
        return true;
    }
}
