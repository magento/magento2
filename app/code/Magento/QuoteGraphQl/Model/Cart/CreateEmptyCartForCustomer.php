<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Cart;

use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Magento\Quote\Model\ResourceModel\Quote\QuoteIdMask as QuoteIdMaskResourceModel;

/**
 * Create empty cart for customer
 */
class CreateEmptyCartForCustomer
{
    /**
     * @var CartManagementInterface
     */
    private $cartManagement;

    /**
     * @var QuoteIdMaskFactory
     */
    private $quoteIdMaskFactory;

    /**
     * @var QuoteIdMaskResourceModel
     */
    private $quoteIdMaskResourceModel;

    /**
     * @param CartManagementInterface $cartManagement
     * @param QuoteIdMaskFactory $quoteIdMaskFactory
     * @param QuoteIdMaskResourceModel $quoteIdMaskResourceModel
     */
    public function __construct(
        CartManagementInterface $cartManagement,
        QuoteIdMaskFactory $quoteIdMaskFactory,
        QuoteIdMaskResourceModel $quoteIdMaskResourceModel
    ) {
        $this->cartManagement = $cartManagement;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->quoteIdMaskResourceModel = $quoteIdMaskResourceModel;
    }

    /**
     * Create empty cart for customer
     *
     * @param int $customerId
     * @param string|null $predefinedMaskedQuoteId
     * @return string
     */
    public function execute(int $customerId, string $predefinedMaskedQuoteId = null): string
    {
        $quoteId = $this->cartManagement->createEmptyCartForCustomer($customerId);

        $quoteIdMask = $this->quoteIdMaskFactory->create();
        $quoteIdMask->setQuoteId($quoteId);

        if (isset($predefinedMaskedQuoteId)) {
            $quoteIdMask->setMaskedId($predefinedMaskedQuoteId);
        }

        $this->quoteIdMaskResourceModel->save($quoteIdMask);
        return $quoteIdMask->getMaskedId();
    }
}
