<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Multishipping\Model\Cart;

use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Cart\CartTotalRepository;
use Magento\Quote\Model\Cart\Totals;

/**
 * CartTotalPlugin calculate total shipping price for multishipping
 */
class CartTotalRepositoryPlugin
{
    /**
     * Quote repository.
     *
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @param CartRepositoryInterface $quoteRepository
     */
    public function __construct(
        CartRepositoryInterface $quoteRepository
    ) {
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * Overwrite the CartTotalRepository quoteTotal and update the shipping price
     *
     * @param                                         CartTotalRepository $subject
     * @param                                         Totals              $quoteTotals
     * @param                                         String              $cartId
     * @return                                        Totals
     * @throws                                        \Magento\Framework\Exception\NoSuchEntityException
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGet(
        CartTotalRepository $subject,
        Totals $quoteTotals,
        String $cartId
    ) {
        $quote = $this->quoteRepository->getActive($cartId);
        if ($quote->getIsMultiShipping()) {
            $shippingMethod = $quote->getShippingAddress()->getShippingMethod();
            if (isset($shippingMethod) && !empty($shippingMethod)) {
                $shippingRate = $quote->getShippingAddress()->getShippingRateByCode($shippingMethod);
                $shippingPrice = $shippingRate->getPrice();
            } else {
                $shippingPrice = $quote->getShippingAddress()->getShippingAmount();
            }
            /**
             * @var \Magento\Store\Api\Data\StoreInterface
             */
            $store = $quote->getStore();
            $amountPrice = $store->getBaseCurrency()
                ->convert($shippingPrice, $store->getCurrentCurrencyCode());
            $quoteTotals->setBaseShippingAmount($shippingPrice);
            $quoteTotals->setShippingAmount($amountPrice);
        }
        return $quoteTotals;
    }
}
