<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Cart;

use Magento\Quote\Api\GuestCartManagementInterface;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Magento\Quote\Model\ResourceModel\Quote\QuoteIdMask as QuoteIdMaskResourceModel;

/**
 * Create empty cart for guest
 */
class CreateEmptyCartForGuest
{
    /**
     * @var GuestCartManagementInterface
     */
    private $guestCartManagement;

    /**
     * @var QuoteIdMaskFactory
     */
    private $quoteIdMaskFactory;

    /**
     * @var QuoteIdMaskResourceModel
     */
    private $quoteIdMaskResourceModel;

    /**
     * @param GuestCartManagementInterface $guestCartManagement
     * @param QuoteIdMaskFactory $quoteIdMaskFactory
     * @param QuoteIdMaskResourceModel $quoteIdMaskResourceModel
     */
    public function __construct(
        GuestCartManagementInterface $guestCartManagement,
        QuoteIdMaskFactory $quoteIdMaskFactory,
        QuoteIdMaskResourceModel $quoteIdMaskResourceModel
    ) {
        $this->guestCartManagement = $guestCartManagement;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->quoteIdMaskResourceModel = $quoteIdMaskResourceModel;
    }

    /**
     * Create empty cart for guest
     *
     * @param string|null $predefinedMaskedQuoteId
     * @return string
     */
    public function execute(string $predefinedMaskedQuoteId = null): string
    {
        $maskedQuoteId = $this->guestCartManagement->createEmptyCart();

        if (isset($predefinedMaskedQuoteId)) {
            $quoteIdMask = $this->quoteIdMaskFactory->create();
            $this->quoteIdMaskResourceModel->load($quoteIdMask, $maskedQuoteId, 'masked_id');

            $quoteIdMask->setMaskedId($predefinedMaskedQuoteId);
            $this->quoteIdMaskResourceModel->save($quoteIdMask);
        }
        return $predefinedMaskedQuoteId ?? $maskedQuoteId;
    }
}
