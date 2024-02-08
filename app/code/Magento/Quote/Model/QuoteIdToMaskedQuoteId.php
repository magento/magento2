<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Model;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\ResourceModel\Quote as QuoteResource;
use Magento\Quote\Model\ResourceModel\Quote\QuoteIdMask as QuoteIdMaskResource;

/**
 * QuoteId to MaskedQuoteId resolver
 */
class QuoteIdToMaskedQuoteId implements QuoteIdToMaskedQuoteIdInterface
{
    /**
     * @var QuoteIdMaskResource
     */
    private $quoteIdMaskResource;

    /**
     * @var QuoteResource
     */
    private $quoteResource;

    /**
     * @param QuoteIdMaskFactory $quoteIdMaskFactory
     * @param CartRepositoryInterface $cartRepository
     * @param QuoteIdMaskResource $quoteIdMaskResource
     * @param QuoteResource|null $quoteResourceModel
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        QuoteIdMaskFactory $quoteIdMaskFactory,
        CartRepositoryInterface $cartRepository,
        QuoteIdMaskResource $quoteIdMaskResource,
        QuoteResource $quoteResourceModel = null
    ) {
        $this->quoteIdMaskResource = $quoteIdMaskResource;
        $this->quoteResource = $quoteResourceModel ?? ObjectManager::getInstance()->get(QuoteResource::class);
    }

    /**
     * @inheritDoc
     */
    public function execute(int $quoteId): string
    {
        // Check the quote exists to avoid database constraint issues
        if (!$this->quoteResource->isExists($quoteId)) {
            throw new NoSuchEntityException(
                __(
                    'No such entity with %fieldName = %fieldValue',
                    [
                        'fieldName' => 'quoteId',
                        'fieldValue' => $quoteId
                    ]
                )
            );
        }

        return (string)$this->quoteIdMaskResource->getMaskedQuoteId($quoteId);
    }
}
