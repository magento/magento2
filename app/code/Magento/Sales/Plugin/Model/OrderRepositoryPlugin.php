<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sales\Plugin\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\OrderRepository;

/**
 * Plugin for OrderRepository to add comprehensive order validation
 */
class OrderRepositoryPlugin
{
    /**
     * Validate order before save
     *
     * @param OrderRepository $subject
     * @param OrderInterface $entity
     * @return array
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeSave(OrderRepository $subject, OrderInterface $entity): array
    {
        $this->validateBillingAddress($entity);
        $this->validateOrderItems($entity);
        return [$entity];
    }

    /**
     * Validate billing address exists and has required fields
     *
     * @param OrderInterface $entity
     * @throws LocalizedException
     */
    private function validateBillingAddress(OrderInterface $entity): void
    {
        $billingAddress = $entity->getBillingAddress();
        if (!$billingAddress ||
            !$billingAddress->getFirstname() ||
            !$billingAddress->getLastname()) {
            throw new LocalizedException(__('Please provide billing address for the order.'));
        }
    }

    /**
     * Validate order has items
     *
     * @param OrderInterface $entity
     * @throws LocalizedException
     */
    private function validateOrderItems(OrderInterface $entity): void
    {
        $items = $entity->getAllVisibleItems();

        if (!$items || count($items) === 0) {
            throw new LocalizedException(__('Please specify order items.'));
        }
    }
}
