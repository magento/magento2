<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Observer;

use Magento\Store\Model\StoresConfig;

/**
 * Class CleanExpiredQuotes
 */
class CleanExpiredQuotes
{
    const LIFETIME = 86400;

    /**
     * @var StoresConfig
     */
    protected $storesConfig;

    /**
     * @var \Magento\Sales\Model\Resource\Quote\CollectionFactory
     */
    protected $quoteCollectionFactory;

    /**
     * @var array
     */
    protected $expireQuotesFilterFields = [];

    /**
     * @param StoresConfig $storesConfig
     * @param \Magento\Sales\Model\Resource\Quote\CollectionFactory $collectionFactory
     */
    public function __construct(
        StoresConfig $storesConfig,
        \Magento\Sales\Model\Resource\Quote\CollectionFactory $collectionFactory
    ) {
        $this->storesConfig = $storesConfig;
        $this->quoteCollectionFactory = $collectionFactory;
    }

    /**
     * Clean expired quotes (cron process)
     *
     * @return void
     */
    public function execute()
    {
        $lifetimes = $this->storesConfig->getStoresConfigByPath('checkout/cart/delete_quote_after');
        foreach ($lifetimes as $storeId => $lifetime) {
            $lifetime *= self::LIFETIME;

            /** @var $quotes \Magento\Sales\Model\Resource\Quote\Collection */
            $quotes = $this->quoteCollectionFactory->create();

            $quotes->addFieldToFilter('store_id', $storeId);
            $quotes->addFieldToFilter('updated_at', ['to' => date("Y-m-d", time() - $lifetime)]);
            $quotes->addFieldToFilter('is_active', 0);

            foreach ($this->getExpireQuotesAdditionalFilterFields() as $field => $condition) {
                $quotes->addFieldToFilter($field, $condition);
            }

            $quotes->walk('delete');
        }
    }

    /**
     * Retrieve expire quotes additional fields to filter
     *
     * @return array
     */
    public function getExpireQuotesAdditionalFilterFields()
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
