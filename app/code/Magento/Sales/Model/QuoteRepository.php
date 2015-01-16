<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;

class QuoteRepository
{
    /**
     * @var Quote[]
     */
    protected $quotesById = [];

    /**
     * @var Quote[]
     */
    protected $quotesByCustomerId = [];

    /**
     * @var QuoteFactory
     */
    protected $quoteFactory;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param QuoteFactory $quoteFactory
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        QuoteFactory $quoteFactory,
        StoreManagerInterface $storeManager
    ) {
        $this->quoteFactory = $quoteFactory;
        $this->storeManager = $storeManager;
    }

    /**
     * Create new quote
     *
     * @param array $data
     * @return Quote
     */
    public function create(array $data = [])
    {
        return $this->quoteFactory->create($data);
    }

    /**
     * Get quote by id
     *
     * @param int $cartId
     * @param int[] $sharedStoreIds
     * @throws NoSuchEntityException
     * @return Quote
     */
    public function get($cartId, array $sharedStoreIds = [])
    {
        if (!isset($this->quotesById[$cartId])) {
            $quote = $this->loadQuote('load', 'cartId', $cartId, $sharedStoreIds);
            $this->quotesById[$cartId] = $quote;
            $this->quotesByCustomerId[$quote->getCustomerId()] = $quote;
        }
        return $this->quotesById[$cartId];
    }

    /**
     * Get quote by customer Id
     *
     * @param int $customerId
     * @param int[] $sharedStoreIds
     * @return Quote
     * @throws NoSuchEntityException
     */
    public function getForCustomer($customerId, array $sharedStoreIds = [])
    {
        if (!isset($this->quotesByCustomerId[$customerId])) {
            $quote = $this->loadQuote('loadByCustomer', 'customerId', $customerId, $sharedStoreIds);
            $this->quotesById[$quote->getId()] = $quote;
            $this->quotesByCustomerId[$customerId] = $quote;
        }
        return $this->quotesByCustomerId[$customerId];
    }

    /**
     * Get active quote by id
     *
     * @param int $cartId
     * @param int[] $sharedStoreIds
     * @return Quote
     * @throws NoSuchEntityException
     */
    public function getActive($cartId, array $sharedStoreIds = [])
    {
        $quote = $this->get($cartId, $sharedStoreIds);
        if (!$quote->getIsActive()) {
            throw NoSuchEntityException::singleField('cartId', $cartId);
        }
        return $quote;
    }

    /**
     * Get active quote by customer Id
     *
     * @param int $customerId
     * @param int[] $sharedStoreIds
     * @return Quote
     * @throws NoSuchEntityException
     */
    public function getActiveForCustomer($customerId, array $sharedStoreIds = [])
    {
        $quote = $this->getForCustomer($customerId, $sharedStoreIds);
        if (!$quote->getIsActive()) {
            throw NoSuchEntityException::singleField('customerId', $customerId);
        }
        return $quote;
    }

    /**
     * Save quote
     *
     * @param Quote $quote
     * @return void
     */
    public function save(Quote $quote)
    {
        $quote->save();
        unset($this->quotesById[$quote->getId()]);
        unset($this->quotesByCustomerId[$quote->getCustomerId()]);
    }

    /**
     * Delete quote
     *
     * @param Quote $quote
     * @return void
     */
    public function delete(Quote $quote)
    {
        $quoteId = $quote->getId();
        $customerId = $quote->getCustomerId();
        $quote->delete();
        unset($this->quotesById[$quoteId]);
        unset($this->quotesByCustomerId[$customerId]);
    }

    /**
     * Load quote with different methods
     *
     * @param string $loadMethod
     * @param string $loadField
     * @param int $identifier
     * @param int[] $sharedStoreIds
     * @throws NoSuchEntityException
     * @return Quote
     */
    protected function loadQuote($loadMethod, $loadField, $identifier, array $sharedStoreIds = [])
    {
        /** @var Quote $quote */
        $quote = $this->quoteFactory->create();
        if ($sharedStoreIds) {
            $quote->setSharedStoreIds($sharedStoreIds);
        }
        $quote->setStoreId($this->storeManager->getStore()->getId())->$loadMethod($identifier);
        if (!$quote->getId()) {
            throw NoSuchEntityException::singleField($loadField, $identifier);
        }
        return $quote;
    }
}
