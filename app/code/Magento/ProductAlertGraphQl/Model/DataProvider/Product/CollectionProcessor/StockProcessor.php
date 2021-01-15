<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ProductAlertGraphQl\Model\DataProvider\Product\CollectionProcessor;

use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\Product\CollectionProcessorInterface;
use Magento\GraphQl\Model\Query\ContextInterface;
use Magento\ProductAlert\Helper\Data as AlertsHelper;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\App\ResourceConnection;

/**
 * Add stock alert field for product collection
 */
class StockProcessor implements CollectionProcessorInterface
{
    /**
     * @var AlertsHelper
     */
    private $helper;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @param AlertsHelper $helper
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        AlertsHelper $helper,
        ResourceConnection $resourceConnection
    ) {
        $this->helper = $helper;
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * Process collection to add additional joins, attributes, and clauses to a product collection.
     *
     * @param Collection $collection
     * @param SearchCriteriaInterface $searchCriteria
     * @param array $attributeNames
     * @param ContextInterface|null $context
     * @return Collection
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function process(
        Collection $collection,
        SearchCriteriaInterface $searchCriteria,
        array $attributeNames,
        ContextInterface $context = null
    ): Collection {
        $connection = $this->resourceConnection->getConnection();

        if ($this->helper->isStockAlertAllowed() &&
            $context !== null &&
            $context->getUserId() &&
            in_array('productalert_stock_subscribed', $attributeNames)) {
            $store = $context->getExtensionAttributes()->getStore();
            $joinCondition = 'alert_stock.product_id = e.entity_id AND ' .
                $connection->quoteInto('alert_stock.customer_id = ?', $context->getUserId()) .
                ' AND ' .
                $connection->quoteInto('alert_stock.store_id = ?', $store->getId());

            $collection->getSelect()->joinLeft(
                ['alert_stock' => $connection->getTableName('product_alert_stock')],
                $joinCondition,
                ['productalert_stock_subscribed' => 'alert_stock.alert_stock_id']
            );
        }

        return $collection;
    }
}
