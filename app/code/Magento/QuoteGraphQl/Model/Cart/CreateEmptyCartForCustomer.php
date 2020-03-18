<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Cart;

use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Magento\Quote\Model\QuoteIdToMaskedQuoteIdInterface;
use Magento\Quote\Model\ResourceModel\Quote\QuoteIdMask as QuoteIdMaskResourceModel;
use Magento\Quote\Model\Cart\CustomerCartProvider;
use Magento\Framework\App\ObjectManager;

/**
 * Create empty cart for customer
 */
class CreateEmptyCartForCustomer
{
    /**
     * @var QuoteIdToMaskedQuoteIdInterface
     */
    private $quoteIdToMaskedQuoteId;

    /**
     * @var CustomerCartProvider
     */
    private $cartProvider;

    /**
     * @param CartManagementInterface $cartManagement
     * @param QuoteIdMaskFactory $quoteIdMaskFactory
     * @param QuoteIdMaskResourceModel $quoteIdMaskResourceModel
     * @param QuoteIdToMaskedQuoteIdInterface $quoteIdToMaskedQuoteId
     * @param CustomerCartProvider $cartProvider
     * @SuppressWarnings(PHPMD.UnusedFormalParameter) Parameters can't be removed according to backward compatibility
     */
    public function __construct(
        CartManagementInterface $cartManagement,
        QuoteIdMaskFactory $quoteIdMaskFactory,
        QuoteIdMaskResourceModel $quoteIdMaskResourceModel,
        QuoteIdToMaskedQuoteIdInterface $quoteIdToMaskedQuoteId,
        CustomerCartProvider $cartProvider
    ) {
        $this->quoteIdToMaskedQuoteId = $quoteIdToMaskedQuoteId;
        $this->cartProvider = $cartProvider ?? ObjectManager::getInstance()->get(CustomerCartProvider::class);
    }

    /**
     * Create empty cart for customer
     *
     * @param int $customerId
     * @param string|null $predefinedMaskedQuoteId
     * @return string
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter) Parameter can't be removed according to backward compatibility
     */
    public function execute(int $customerId, string $predefinedMaskedQuoteId = null): string
    {
        $cart = $this->cartProvider->provide($customerId);
        $quoteId = (int) $cart->getId();

        return $this->quoteIdToMaskedQuoteId->execute($quoteId);
    }
}
