<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Model;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\Data\CartInterfaceFactory;
use Magento\Quote\Model\ResourceModel\Quote\QuoteIdMask as QuoteIdMaskResource;
use Magento\Store\Model\StoreManagerInterface;

/**
 * QuoteId to MaskedQuoteId resolver
 */
class QuoteIdToMaskedQuoteId implements QuoteIdToMaskedQuoteIdInterface
{
    /**
     * @var QuoteIdMaskFactory
     */
    private $quoteIdMaskFactory;

    /**
     * @var QuoteIdMaskResource
     */
    private $quoteIdMaskResource;
    /**
     * @var CartInterfaceFactory
     */
    private $cartFactory;
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param QuoteIdMaskFactory $quoteIdMaskFactory
     * @param QuoteIdMaskResource $quoteIdMaskResource
     * @param CartInterfaceFactory $cartInterfaceFactory
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        QuoteIdMaskFactory $quoteIdMaskFactory,
        QuoteIdMaskResource $quoteIdMaskResource,
        CartInterfaceFactory $cartInterfaceFactory,
        StoreManagerInterface $storeManager
    ) {
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->quoteIdMaskResource = $quoteIdMaskResource;
        $this->cartFactory = $cartInterfaceFactory;
        $this->storeManager = $storeManager;
    }

    /**
     * @inheritDoc
     */
    public function execute(int $quoteId): string
    {
        // Check the quote exists to avoid database constraint issues
        $this->checkIfQuoteExists($quoteId);

        $quoteIdMask = $this->quoteIdMaskFactory->create();
        $this->quoteIdMaskResource->load($quoteIdMask, $quoteId, 'quote_id');

        return $quoteIdMask->getMaskedId() ?? '';
    }

    /**
     * This is a tiny implementation similar to the get method in \Magento\Quote\Api\CartRepositoryInterface
     * that isn't loading all quote items, to ensure if quote exists
     *
     * @param int $quoteId
     * @return void
     * @throws NoSuchEntityException
     */
    private function checkIfQuoteExists(int $quoteId): void
    {
        $quote = $this->cartFactory->create();

        $quote
            ->setStoreId($this->storeManager->getStore()->getId())
            ->loadByIdWithoutStore($quoteId);

        if (!$quote->getId()) {
            throw new NoSuchEntityException(
                __(
                    'No such entity with %fieldName = %fieldValue',
                    [
                        'fieldName' => 'cartId',
                        'fieldValue' => $quoteId
                    ]
                )
            );
        }
    }
}
