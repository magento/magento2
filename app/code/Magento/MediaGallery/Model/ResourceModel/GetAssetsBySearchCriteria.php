<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGallery\Model\ResourceModel;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\ResourceConnection;
use Psr\Log\LoggerInterface;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\Search\SearchResultFactory;
use Magento\Framework\DB\Select;
use Magento\MediaGalleryApi\Api\Data\AssetInterface;

/**
 * Get assets data  by searchCriteria
 */
class GetAssetsBySearchCriteria
{
    private const TABLE_MEDIA_GALLERY_ASSET = 'media_gallery_asset';

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var SearchResultFactory
     */
    private $searchResultFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param SearchResultFactory $searchResultFactory
     * @param ResourceConnection $resourceConnection
     * @param LoggerInterface $logger
     */
    public function __construct(
        SearchResultFactory $searchResultFactory,
        ResourceConnection $resourceConnection,
        LoggerInterface $logger
    ) {
        $this->searchResultFactory = $searchResultFactory;
        $this->resourceConnection = $resourceConnection;
        $this->logger = $logger;
    }

    /**
     * Retrieve assets data from database
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return SearchResultInterface
     */
    public function execute(SearchCriteriaInterface $searchCriteria): SearchResultInterface
    {
        $searchResult = $this->searchResultFactory->create();
        $fields = [];
        $conditions = [];
       
        foreach ($searchCriteria->getFilterGroups() as $filterGroup) {
            foreach ($filterGroup->getFilters() as $filter) {
                $condition = $filter->getConditionType() ? $filter->getConditionType() : 'eq';
                $fields[] = $filter->getField();

                if ($condition === 'fulltext') {
                    $condition = 'like';
                    $filter->setValue('%' . $filter->getValue() . '%');
                }

                $conditions[] = [$condition => $filter->getValue()];
            }
        }
        
        if ($fields) {
            $resultCondition = $this->getResultCondition($fields, $conditions);
            $select = $this->resourceConnection->getConnection()->select()
                ->from(
                    $this->resourceConnection->getTableName(self::TABLE_MEDIA_GALLERY_ASSET)
                )
                ->where($resultCondition, null, Select::TYPE_CONDITION);

            if ($searchCriteria->getPageSize() || $searchCriteria->getCurrentPage()) {
                $select->limit(
                    $searchCriteria->getPageSize(),
                    $searchCriteria->getCurrentPage() * $searchCriteria->getPageSize()
                );
            }
        
            $data = $this->resourceConnection->getConnection()->fetchAll($select);
        }
        
        $searchResult->setSearchCriteria($searchCriteria);
        $searchResult->setItems($data);
       
        return $searchResult;
    }

    /**
     * Get conditions data by searchCriteria
     *
     * @param string|array $field
     * @param null|string|array $condition
     */
    public function getResultCondition($field, $condition = null)
    {
        $resourceConnection = $this->resourceConnection->getConnection();
        if (is_array($field)) {
            $conditions = [];
            foreach ($field as $key => $value) {
                $conditions[] = $resourceConnection->prepareSqlCondition(
                    $resourceConnection->quoteIdentifier($value),
                    isset($condition[$key]) ? $condition[$key] : null
                );
            }

            $resultCondition = '(' . implode(') ' . Select::SQL_OR . ' (', $conditions) . ')';
        } else {
            $resultCondition = $resourceConnection->prepareSqlCondition(
                $resourceConnection->quoteIdentifier($field),
                $condition
            );
        }
        return $resultCondition;
    }
}
