<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogRule\Model\ResourceModel\Product;

use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\CatalogRule\Pricing\Price\CatalogRulePrice;
use Magento\Framework\App\ObjectManager;

/**
 * Add catalog rule prices to collection
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class CollectionProcessor
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resource;

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    private $dateTime;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    private $localeDate;

    /**
     * @var \Magento\CatalogRule\Model\RuleDateFormatterInterface
     */
    private $ruleDateFormatter;

    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\CatalogRule\Model\RuleDateFormatterInterface|null $ruleDateFormatter
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\CatalogRule\Model\RuleDateFormatterInterface $ruleDateFormatter = null
    ) {
        $this->storeManager = $storeManager;
        $this->resource = $resourceConnection;
        $this->customerSession = $customerSession;
        $this->dateTime = $dateTime;
        $this->localeDate = $localeDate;
        $this->ruleDateFormatter = $ruleDateFormatter ?: ObjectManager::getInstance()
            ->get(\Magento\CatalogRule\Model\RuleDateFormatterInterface::class);
    }

    /**
     * Join prices to collection
     *
     * @param ProductCollection $productCollection
     * @param string $joinColumn
     * @return ProductCollection
     */
    public function addPriceData(ProductCollection $productCollection, $joinColumn = 'e.entity_id')
    {
        if (!$productCollection->hasFlag('catalog_rule_loaded')) {
            $connection = $this->resource->getConnection();
            $store = $this->storeManager->getStore();
            $productCollection->getSelect()
                ->joinLeft(
                    ['catalog_rule' => $this->resource->getTableName('catalogrule_product_price')],
                    implode(
                        ' AND ',
                        [
                            'catalog_rule.product_id = ' . $connection->quoteIdentifier($joinColumn),
                            $connection->quoteInto('catalog_rule.website_id = ?', $store->getWebsiteId()),
                            $connection->quoteInto(
                                'catalog_rule.customer_group_id = ?',
                                $this->customerSession->getCustomerGroupId()
                            ),
                            $connection->quoteInto(
                                'catalog_rule.rule_date = ?',
                                $this->dateTime->formatDate($this->ruleDateFormatter->getDate($store->getId()), false)
                            ),
                        ]
                    ),
                    [CatalogRulePrice::PRICE_CODE => 'rule_price']
                );
            $productCollection->setFlag('catalog_rule_loaded', true);
        }

        return $productCollection;
    }
}
