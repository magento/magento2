<?php
/************************************************************************
 * Copyright 2025 Adobe
 * All Rights Reserved.
 *
 * NOTICE: All information contained herein is, and remains
 * the property of Adobe and its suppliers, if any. The intellectual
 * and technical concepts contained herein are proprietary to Adobe
 * and its suppliers and are protected by all applicable intellectual
 * property laws, including trade secret and copyright laws.
 * Dissemination of this information or reproduction of this material
 * is strictly forbidden unless prior written permission is obtained
 * from Adobe.
 * ***********************************************************************
 */

declare(strict_types=1);

namespace Magento\Quote\Plugin;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Webapi\Rest\Request as RestRequest;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

/**
 * Validate order id from request param
 */
class ValidateQuoteOrigOrder
{
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(OrderRepositoryInterface $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }

    /**
     * Validate the user authorization to order
     *
     * @param CartRepositoryInterface $cartRepository
     * @param CartInterface $quote
     * @return void
     * @throws NoSuchEntityException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeSave(
        CartRepositoryInterface $cartRepository,
        CartInterface $quote
    ): void {
        if ($quote->getOrigOrderId() && $quote->getCustomerId()) {
            $order = $this->orderRepository->get((int)$quote->getOrigOrderId());
            $orderCustomer = (int)$order->getCustomerId();
            if ((int)$quote->getCustomerId() !== $orderCustomer) {
                throw new NoSuchEntityException(__('Please check input parameters.'));
            }
        }
    }
}
