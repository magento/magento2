<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Cron;

use Magento\Store\Model\StoresConfig;

/**
 * Class CleanExpiredQuotes
 * @since 2.0.0
 */
class CleanExpiredQuotes
{
    const LIFETIME = 86400;

    /**
     * @var StoresConfig
     * @since 2.0.0
     */
    protected $storesConfig;

    /**
     * @var \Magento\Quote\Model\ResourceModel\Quote\CollectionFactory
     * @since 2.0.0
     */
    protected $quoteCollectionFactory;

    /**
     * @var array
     * @since 2.0.0
     */
    protected $expireQuotesFilterFields = [];

    /**
     * @param StoresConfig $storesConfig
     * @param \Magento\Quote\Model\ResourceModel\Quote\CollectionFactory $collectionFactory
     * @since 2.0.0
     */
    public function __construct(
        StoresConfig $storesConfig,
        \Magento\Quote\Model\ResourceModel\Quote\CollectionFactory $collectionFactory
    ) {
        $this->storesConfig = $storesConfig;
        $this->quoteCollectionFactory = $collectionFactory;
    }

    /**
     * Clean expired quotes (cron process)
     *
     * @return void
     * @since 2.0.0
     */
    public function execute()
    {
        $lifetimes = $this->storesConfig->getStoresConfigByPath('checkout/cart/delete_quote_after');
        foreach ($lifetimes as $storeId => $lifetime) {
            $lifetime *= self::LIFETIME;

            /** @var $quotes \Magento\Quote\Model\ResourceModel\Quote\Collection */
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
     * @since 2.0.0
     */
    protected function getExpireQuotesAdditionalFilterFields()
    {
        return $this->expireQuotesFilterFields;
    }

    /**
     * Set expire quotes additional fields to filter
     *
     * @param array $fields
     * @return void
     * @since 2.0.0
     */
    public function setExpireQuotesAdditionalFilterFields(array $fields)
    {
        $this->expireQuotesFilterFields = $fields;
    }
}
