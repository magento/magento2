<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Quote\Model\GuestCart;

use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteIdMask;
use Magento\Quote\Model\QuoteRepository;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Quote\Model\QuoteIdMaskFactory;

/**
 * Class GuestCartRepository
 */
class GuestCartRepository extends QuoteRepository
{
    /**
     * @var QuoteIdMaskFactory
     */
    protected $quoteIdMaskFactory;

    /**
     * @param QuoteFactory $quoteFactory
     * @param StoreManagerInterface $storeManager
     * @param \Magento\Quote\Model\Resource\Quote\Collection $quoteCollection
     * @param \Magento\Quote\Api\Data\CartSearchResultsInterfaceFactory $searchResultsDataFactory
     * @param QuoteIdMaskFactory $quoteIdMaskFactory
     */
    public function __construct(
        QuoteFactory $quoteFactory,
        StoreManagerInterface $storeManager,
        \Magento\Quote\Model\Resource\Quote\Collection $quoteCollection,
        \Magento\Quote\Api\Data\CartSearchResultsInterfaceFactory $searchResultsDataFactory,
        QuoteIdMaskFactory $quoteIdMaskFactory
    ) {
        parent::__construct($quoteFactory, $storeManager, $quoteCollection, $searchResultsDataFactory);
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
    }

    /**
     * @inheritdoc
     */
    public function get($cartId, array $sharedStoreIds = [])
    {
        /** @var $quoteIdMask QuoteIdMask */
        $quoteIdMask = $this->quoteIdMaskFactory->loadByMaskedId($cartId);
        return parent::get($quoteIdMask->getId(), $sharedStoreIds);
    }

    /**
     * @inheritdoc
     */
    public function getActive($cartId, array $sharedStoreIds = [])
    {
        /** @var $quoteIdMask QuoteIdMask */
        $quoteIdMask = $this->quoteIdMaskFactory->loadByMaskedId($cartId);
        return parent::getActive($quoteIdMask->getId(), $sharedStoreIds);
    }

    /**
     * @inheritdoc
     */
    public function save(Quote $quote)
    {
        if($quote->getId()) {
            /** @var $quoteIdMask QuoteIdMask */
            $quoteIdMask = $this->quoteIdMaskFactory->loadByMaskedId($quote->getId());
            $quote->setId($quoteIdMask->getId());
        }
        parent::save($quote);
    }

    /**
     * @inheritdoc
     */
    public function delete(Quote $quote)
    {
        if($quote->getId()) {
            /** @var $quoteIdMask QuoteIdMask */
            $quoteIdMask = $this->quoteIdMaskFactory->loadByMaskedId($quote->getId());
            $quote->setId($quoteIdMask->getId());
        }
        parent::delete($quote);
    }
}
