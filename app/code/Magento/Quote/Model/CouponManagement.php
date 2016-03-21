<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Quote\Model;

use \Magento\Quote\Api\CouponManagementInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Coupon management object.
 */
class CouponManagement implements CouponManagementInterface
{
    /**
     * Quote repository.
     *
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * Constructs a coupon read service object.
     *
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository Quote repository.
     */
    public function __construct(
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
    ) {
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function get($cartId)
    {
        /** @var  \Magento\Quote\Model\Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);
        return $quote->getCouponCode();
    }

    /**
     * {@inheritdoc}
     */
    public function set($cartId, $couponCode)
    {
        /** @var  \Magento\Quote\Model\Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);
        if (!$quote->getItemsCount()) {
            throw new NoSuchEntityException(__('Cart %1 doesn\'t contain products', $cartId));
        }
        $quote->getShippingAddress()->setCollectShippingRates(true);

        try {
            $quote->setCouponCode($couponCode);
            $this->quoteRepository->save($quote->collectTotals());
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__('Could not apply coupon code'));
        }
        if ($quote->getCouponCode() != $couponCode) {
            throw new NoSuchEntityException(__('Coupon code is not valid'));
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function remove($cartId)
    {
        /** @var  \Magento\Quote\Model\Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);
        if (!$quote->getItemsCount()) {
            throw new NoSuchEntityException(__('Cart %1 doesn\'t contain products', $cartId));
        }
        $quote->getShippingAddress()->setCollectShippingRates(true);
        try {
            $quote->setCouponCode('');
            $this->quoteRepository->save($quote->collectTotals());
        } catch (\Exception $e) {
            throw new CouldNotDeleteException(__('Could not delete coupon code'));
        }
        if ($quote->getCouponCode() != '') {
            throw new CouldNotDeleteException(__('Could not delete coupon code'));
        }
        return true;
    }
}
