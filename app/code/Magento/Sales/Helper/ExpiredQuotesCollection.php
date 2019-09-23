<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Quote\Model\ResourceModel\Quote\Collection;
use Magento\Quote\Model\ResourceModel\Quote\CollectionFactory;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Class ExpiredQuotesCollection
 */
class ExpiredQuotesCollection
{
    const SECONDS_IN_DAY = 86400;
    const QUOTE_LIFETIME = 'checkout/cart/delete_quote_after';

    /**
     * @var CollectionFactory
     */
    private $quoteCollectionFactory;

    /**
     * @var array
     */
    private $expireQuotesFilterFields = [];

    /**
     * @var ScopeConfigInterface
     */
    private $config;

    /**
     * @param ScopeConfigInterface $config
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        ScopeConfigInterface $config,
        CollectionFactory $collectionFactory
    ) {
        $this->config = $config;
        $this->quoteCollectionFactory = $collectionFactory;
    }

    /**
     * Gets expired quotes
     *
     * Quote is considered expired if the latest update date
     * of the quote is greater than lifetime threshold
     *
     * @param StoreInterface $store
     * @return Collection
     */
    public function getExpiredQuotes(StoreInterface $store)
    {
        $lifetime = $this->config->getValue(
            self::QUOTE_LIFETIME,
            ScopeInterface::SCOPE_STORE,
            $store->getCode()
        );
        $lifetime *= self::SECONDS_IN_DAY;

        /** @var $quotes Collection */
        $quotes = $this->quoteCollectionFactory->create();
        $quotes->addFieldToFilter('store_id', $store->getId());
        $quotes->addFieldToFilter('updated_at', ['to' => date("Y-m-d", time() - $lifetime)]);

        foreach ($this->getExpireQuotesAdditionalFilterFields() as $field => $condition) {
            $quotes->addFieldToFilter($field, $condition);
        }

        return $quotes;
    }

    /**
     * Retrieve expire quotes additional fields to filter
     *
     * @return array
     */
    private function getExpireQuotesAdditionalFilterFields()
    {
        return $this->expireQuotesFilterFields;
    }

    /**
     * Set expire quotes additional fields to filter
     *
     * @param array $fields
     * @return void
     */
    public function setExpireQuotesAdditionalFilterFields(array $fields)
    {
        $this->expireQuotesFilterFields = $fields;
    }
}
