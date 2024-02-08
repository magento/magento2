<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Fixture;

use Magento\Framework\DataObject;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\GuestCartManagementInterface;
use Magento\Quote\Model\MaskedQuoteIdToQuoteIdInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Model\ResourceModel\Quote as QuoteResource;
use Magento\TestFramework\Fixture\RevertibleDataFixtureInterface;

class GuestCart implements RevertibleDataFixtureInterface
{
    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var MaskedQuoteIdToQuoteIdInterface
     */
    private $maskedQuoteIdToQuoteId;

    /**
     * @var GuestCartManagementInterface
     */
    private $guestCartManagement;

    /**
     * @var QuoteResource
     */
    private $quoteResource;

    /**
     * @var QuoteFactory
     */
    private $quoteFactory;

    /**
     * @param CartRepositoryInterface $cartRepository
     * @param MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId
     * @param GuestCartManagementInterface $guestCartManagement
     * @param QuoteResource $quoteResource
     * @param QuoteFactory $quoteFactory
     */
    public function __construct(
        CartRepositoryInterface $cartRepository,
        MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId,
        GuestCartManagementInterface $guestCartManagement,
        QuoteResource $quoteResource,
        QuoteFactory $quoteFactory
    ) {
        $this->cartRepository = $cartRepository;
        $this->maskedQuoteIdToQuoteId = $maskedQuoteIdToQuoteId;
        $this->guestCartManagement = $guestCartManagement;
        $this->quoteResource = $quoteResource;
        $this->quoteFactory = $quoteFactory;
    }

    /**
     * @inheritdoc
     */
    public function apply(array $data = []): ?DataObject
    {
        $maskId = $this->guestCartManagement->createEmptyCart();
        $cartId = $this->maskedQuoteIdToQuoteId->execute($maskId);

        return $this->cartRepository->get($cartId);
    }

    /**
     * @inheritdoc
     */
    public function revert(DataObject $data): void
    {
        /** @var Quote $cart */
        $cart = $data;
        $quote = $this->quoteFactory->create();
        $this->quoteResource->load($quote, $cart->getId());
        if ($quote->getId()) {
            $this->cartRepository->delete($cart);
        }
    }
}
