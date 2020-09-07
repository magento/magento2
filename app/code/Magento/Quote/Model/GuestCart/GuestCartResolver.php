<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Model\GuestCart;

use Magento\Quote\Api\GuestCartManagementInterface;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Magento\Quote\Model\ResourceModel\Quote\QuoteIdMask as QuoteIdMaskResourceModel;
use Magento\Quote\Model\Quote;

/**
 * Return empty cart for guest
 */
class GuestCartResolver
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
     * @var \Magento\Quote\Api\GuestCartRepositoryInterface
     */
    private $guestCartRepository;

    /**
     * @param GuestCartManagementInterface $guestCartManagement
     * @param QuoteIdMaskFactory $quoteIdMaskFactory
     * @param QuoteIdMaskResourceModel $quoteIdMaskResourceModel
     * @param \Magento\Quote\Api\GuestCartRepositoryInterface $guestCartRepository
     */
    public function __construct(
        GuestCartManagementInterface $guestCartManagement,
        QuoteIdMaskFactory $quoteIdMaskFactory,
        QuoteIdMaskResourceModel $quoteIdMaskResourceModel,
        \Magento\Quote\Api\GuestCartRepositoryInterface $guestCartRepository
    ) {
        $this->guestCartManagement = $guestCartManagement;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->quoteIdMaskResourceModel = $quoteIdMaskResourceModel;
        $this->guestCartRepository = $guestCartRepository;
    }

    /**
     * Create empty cart for guest
     *
     * @param string|null $predefinedMaskedQuoteId
     * @return Quote
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function resolve(string $predefinedMaskedQuoteId = null): Quote
    {
        $maskedQuoteId = $this->guestCartManagement->createEmptyCart();

        if ($predefinedMaskedQuoteId !== null) {
            $quoteIdMask = $this->quoteIdMaskFactory->create();
            $this->quoteIdMaskResourceModel->load($quoteIdMask, $maskedQuoteId, 'masked_id');

            $quoteIdMask->setMaskedId($predefinedMaskedQuoteId);
            $this->quoteIdMaskResourceModel->save($quoteIdMask);
            $maskedQuoteId = $predefinedMaskedQuoteId;
        }

        return $this->guestCartRepository->get($maskedQuoteId);
    }
}
