<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Cron;

use Magento\Framework\App\ObjectManager;
use Magento\Quote\Model\ResourceModel\Quote\CollectionFactory as QuoteCollectionFactory;
use Magento\Sales\Model\ExpireQuotesFilterFieldsProvider;
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
     * @var QuoteCollectionFactory
     */
    protected $quoteCollectionFactory;

    /**
     * @var array
     */
    protected $expireQuotesFilterFields = [];

    /**
     * @var ExpireQuotesFilterFieldsProvider
     */
    private $expireQuotesFilterFieldsProvider;

    /**
     * @param StoresConfig $storesConfig
     * @param QuoteCollectionFactory $collectionFactory
     * @param ExpireQuotesFilterFieldsProvider $expireQuotesFilterFieldsProvider
     */
    public function __construct(
        StoresConfig $storesConfig,
        QuoteCollectionFactory $collectionFactory,
        ExpireQuotesFilterFieldsProvider $expireQuotesFilterFieldsProvider = null
    ) {
        $this->storesConfig = $storesConfig;
        $this->quoteCollectionFactory = $collectionFactory;
        $this->expireQuotesFilterFieldsProvider = $expireQuotesFilterFieldsProvider
            ?? ObjectManager::getInstance()->get(ExpireQuotesFilterFieldsProvider::class);
    }

    /**
     * Clean expired quotes (cron process)
     *
     * @return void
     */
    public function execute()
    {
        $lifetimes = $this->storesConfig->getStoresConfigByPath('checkout/cart/delete_quote_after');
        $fields = array_merge(
            $this->getExpireQuotesAdditionalFilterFields(),
            $this->expireQuotesFilterFieldsProvider->getFields()
        );
        foreach ($lifetimes as $storeId => $lifetime) {
            $lifetime *= self::LIFETIME;

            /** @var $quotes \Magento\Quote\Model\ResourceModel\Quote\Collection */
            $quotes = $this->quoteCollectionFactory->create();

            $quotes->addFieldToFilter('store_id', $storeId);
            $quotes->addFieldToFilter('updated_at', ['to' => date("Y-m-d", time() - $lifetime)]);
            $quotes->addFieldToFilter('is_active', 0);

            foreach ($fields as $field => $condition) {
                $quotes->addFieldToFilter($field, $condition);
            }

            $quotes->walk('delete');
        }
    }

    /**
     * Retrieve expire quotes additional fields to filter
     *
     * @return array
     * @deprecated use expireQuotesFilterFieldsProvider::getFields.
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
     * @deprecated inject values to expireQuotesFilterFieldsProvider constructor through di.xml argument node.
     */
    public function setExpireQuotesAdditionalFilterFields(array $fields)
    {
        $this->expireQuotesFilterFields = $fields;
    }
}
