<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Fixture;

use Magento\Framework\DataObject;
use Magento\Framework\Exception\InvalidArgumentException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Model\ResourceModel\Quote as QuoteResource;
use Magento\TestFramework\Fixture\DataFixtureInterface;

/**
 * Mark cart as inactive
 */
class MakeCartInactive implements DataFixtureInterface
{
    private const FIELD_CART_ID = 'cart_id';

    /**
     * @var CartRepositoryInterface
     */
    private CartRepositoryInterface $cartRepository;

    /**
     * @var QuoteFactory
     */
    private QuoteFactory $quoteFactory;

    /**
     * @var QuoteResource
     */
    private QuoteResource $quoteResource;

    /**
     * @param CartRepositoryInterface $cartRepository
     * @param QuoteFactory $quoteFactory
     * @param QuoteResource $quoteResource
     */
    public function __construct(
        CartRepositoryInterface $cartRepository,
        QuoteFactory $quoteFactory,
        QuoteResource $quoteResource
    ) {
        $this->cartRepository = $cartRepository;
        $this->quoteFactory = $quoteFactory;
        $this->quoteResource = $quoteResource;
    }

    /**
     * @param array $data
     * @return void
     * @throws InvalidArgumentException
     */
    public function apply(array $data = []): ?DataObject
    {
        if (empty($data[self::FIELD_CART_ID])) {
            throw new InvalidArgumentException(__('"%field" is required', ['field' => self::FIELD_CART_ID]));
        }

        $quote = $this->quoteFactory->create();
        $this->quoteResource->load($quote, $data[self::FIELD_CART_ID]);
        $quote->setIsActive(false);
        $this->cartRepository->save($quote);

        return $quote;
    }
}
