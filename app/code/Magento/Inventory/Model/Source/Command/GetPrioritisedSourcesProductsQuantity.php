<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Inventory\Model\Source\Command;

use Magento\InventoryApi\Api\GetPrioritisedSourcesProductsQuantityInterface;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryApi\Api\Data\SourceInterface;

/**
 * Class GetPrioritisedSourcesProductsQuantity
 * @package Magento\Inventory\Model\Source\Command
 */
class GetPrioritisedSourcesProductsQuantity implements GetPrioritisedSourcesProductsQuantityInterface
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * GetPrioritisedSourcesProductsQuantity constructor.
     * @param ResourceConnection $resourceConnection
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param LoggerInterface $logger
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        LoggerInterface $logger
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->logger = $logger;
    }

    /**
     * Method return array sources id with quantity per source
     * @param string $productSku
     * @param int $count
     * @return array
     * @throws LocalizedException
     */
    public function execute($productSku = "", $count = 1): array
    {
        try{
            $result = [];
            $connection = $this->resourceConnection->getConnection();

            $select = $connection->select()
                ->from(
                    ['item' => $this->resourceConnection->getTableName("inventory_source_item")]
                )->joinInner(
                    ['source' => $this->resourceConnection->getTableName("inventory_source")],
                    'source.'.SourceInterface::SOURCE_ID.'=item.' . SourceItemInterface::SOURCE_ID
                )->reset(\Zend_Db_Select::COLUMNS)
                ->columns(
                    [
                        'item.' . SourceItemInterface::QUANTITY,
                        'source.' . SourceInterface::SOURCE_ID,
                    ]
                )->where('item.' . SourceItemInterface::SKU . '= ?', $productSku)
                ->where('item.' . SourceItemInterface::STATUS . '= ?', 1)
                ->where('source.' . SourceInterface::ENABLED . '= ?', 1)
                ->order("source." . SourceInterface::PRIORITY);

            $selectedData = $connection->fetchAll($select);

            foreach ($selectedData as $key  => $value) {
                if ($value['quantity'] < $count) {
                    $result[] = $value;
                    $count = $count-$value['quantity'];
                } else if ($value['quantity'] == $count) {
                    $result[] = $value;
                    break;
                } else if ($value['quantity'] > $count) {
                    $value['quantity'] = $count;
                    $result[] = $value;
                    break;
                }
            }
            return $result;
        } catch (\Exception $exe){
            $this->logger->error($exe->getMessage());
            throw new LocalizedException(__('Could not load products per sources.'), $exe);
        }
    }
}
