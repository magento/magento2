<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Fixture;

use Magento\Framework\DataObject;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\TestFramework\Fixture\DataFixtureInterface;

class ApplyCoupon implements DataFixtureInterface
{
    /**
     * @var CartRepositoryInterface
     */
    public CartRepositoryInterface $quoteRepository;

    /**
     * @param CartRepositoryInterface $quoteRepository
     */
    public function __construct(
        CartRepositoryInterface $quoteRepository
    ) {
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * {@inheritdoc}
     *
     * @param array $data Parameters
     * <pre>
     *    $data = [
     *      'cart_id'    => (string) Cart ID. Required.
     *      'coupon_codes' => (array) Coupon Codes. Required.
     *    ]
     * </pre>
     * @throws NoSuchEntityException
     */
    public function apply(array $data = []): ?DataObject
    {
        if (empty($data['cart_id']) || empty($data['coupon_codes'])) {
            throw new \InvalidArgumentException('cart_id or coupon_codes is missing!');
        }
        $quote = $this->quoteRepository->getActive($data['cart_id']);
        $quote->setCouponCode(reset($data['coupon_codes']));
        $this->quoteRepository->save($quote->collectTotals());
        return $quote;
    }
}
