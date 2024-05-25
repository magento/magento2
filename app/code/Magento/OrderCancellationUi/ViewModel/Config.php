<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\OrderCancellationUi\ViewModel;

use Magento\Customer\Model\Session;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\OrderCancellation\Model\CustomerCanCancel;
use Magento\OrderCancellation\Model\Config\Config as CancellationConfig;
use Magento\Sales\Api\OrderRepositoryInterface;

/**
 * Config view Model for order cancellation module
 */
class Config implements ArgumentInterface
{
    /**
     * @var Session
     */
    private Session $customerSession;

    /**
     * @var CancellationConfig
     */
    private CancellationConfig $config;

    /**
     * @var OrderRepositoryInterface
     */
    private OrderRepositoryInterface $orderRepository;

    /**
     * @var CustomerCanCancel
     */
    private CustomerCanCancel $customerCanCancel;

    /**
     * @param Session $customerSession
     * @param CancellationConfig $config
     * @param OrderRepositoryInterface $orderRepository
     * @param CustomerCanCancel $customerCanCancel
     */
    public function __construct(
        Session $customerSession,
        CancellationConfig $config,
        OrderRepositoryInterface $orderRepository,
        CustomerCanCancel $customerCanCancel
    ) {
        $this->customerSession = $customerSession;
        $this->config = $config;
        $this->orderRepository = $orderRepository;
        $this->customerCanCancel = $customerCanCancel;
    }

    /**
     * Check if it is possible to cancel.
     *
     * @param int $orderId
     * @return bool
     */
    public function canCancel(int $orderId): bool
    {
        $order = $this->orderRepository->get($orderId);
        if (!$this->config->isOrderCancellationEnabledForStore((int)$order->getStore()->getStoreId())) {
            return false;
        }
        if (!$this->customerCanCancel->execute($order)) {
            return false;
        }
        return true;
    }

    /**
     * Returns order cancellation reasons.
     *
     * @param int $orderId
     * @return array
     */
    public function getCancellationReasons(int $orderId): array
    {
        if ($this->canCancel($orderId)) {
            return $this->config->getCancellationReasons($this->orderRepository->get($orderId)->getStore());
        }
        return [];
    }
}
