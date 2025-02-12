<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Cart;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Api\PaymentMethodManagementInterface;
use Magento\Quote\Model\Quote;

/**
 * Place an order
 */
class PlaceOrder
{
    /**
     * @var PaymentMethodManagementInterface
     */
    private $paymentManagement;

    /**
     * @var CartManagementInterface
     */
    private $cartManagement;

    /**
     * @param PaymentMethodManagementInterface $paymentManagement
     * @param CartManagementInterface $cartManagement
     */
    public function __construct(
        PaymentMethodManagementInterface $paymentManagement,
        CartManagementInterface $cartManagement
    ) {
        $this->paymentManagement = $paymentManagement;
        $this->cartManagement = $cartManagement;
    }

    /**
     * Place an order
     *
     * @param Quote $cart
     * @param string $maskedCartId
     * @param int $userId
     * @return int
     *
     * @throws LocalizedException
     * @throws NoSuchEntityException
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(Quote $cart, string $maskedCartId, int $userId): int
    {
        $cartId = (int)$cart->getId();
        $paymentMethod = $this->paymentManagement->get($cartId);

        return (int)$this->cartManagement->placeOrder($cartId, $paymentMethod);
    }
}
