<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Quote\Plugin\Webapi;

use Magento\Framework\Webapi\ServiceOutputProcessor;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;

/**
 * Add items of an inactive cart to the GET /V1/carts/:cartId response.
 *
 * The repository load handler skips items for inactive quotes because a quote reloaded with items and shipping
 * assignments cannot be saved through the repository while inactive. Filling the items only when the service
 * result is serialized keeps every repository call made while handling the request unchanged.
 */
class AddInactiveCartItemsToResponse
{
    /**
     * Assign visible items to an inactive cart returned by CartRepositoryInterface::get().
     *
     * @param ServiceOutputProcessor $subject
     * @param mixed $data
     * @param string $serviceClassName
     * @param string $serviceMethodName
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeProcess(
        ServiceOutputProcessor $subject,
        $data,
        $serviceClassName,
        $serviceMethodName
    ): void {
        if ($serviceClassName !== CartRepositoryInterface::class
            || $serviceMethodName !== 'get'
            || !$data instanceof Quote
            || $data->getIsActive()
            || $data->getItems() !== null
        ) {
            return;
        }

        $data->setItems($data->getAllVisibleItems());
    }
}
