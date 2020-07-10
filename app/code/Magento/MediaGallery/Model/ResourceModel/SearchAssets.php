<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGallery\Model\ResourceModel;

use Magento\Framework\Exception\LocalizedException;
use Magento\MediaGalleryApi\Api\Data\AssetInterfaceFactory;
use Magento\MediaGalleryApi\Api\SearchAssetsInterface;
use Magento\Framework\App\ResourceConnection;
use Psr\Log\LoggerInterface;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\Search\SearchResultFactory;
use Magento\Framework\DB\Select;
use Magento\MediaGalleryApi\Api\Data\AssetInterface;

/**
 * Get media assets by searchCriteria
 */
class SearchAssets implements SearchAssetsInterface
{
    private const TABLE_MEDIA_GALLERY_ASSET = 'media_gallery_asset';

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var AssetInterfaceFactory
     */
    private $mediaAssetFactory;

    /**
     * @var SearchResultFactory
     */
    protected $searchResultFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param SearchResultFactory $searchResultFactory
     * @param ResourceConnection $resourceConnection
     * @param AssetInterfaceFactory $mediaAssetFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        SearchResultFactory $searchResultFactory,
        ResourceConnection $resourceConnection,
        AssetInterfaceFactory $mediaAssetFactory,
        LoggerInterface $logger
    ) {
        $this->searchResultFactory = $searchResultFactory;
        $this->resourceConnection = $resourceConnection;
        $this->mediaAssetFactory = $mediaAssetFactory;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function execute(SearchCriteriaInterface $searchCriteria): array
    {
        $assets = [];
        try {
            foreach ($this->getAssetsData($searchCriteria)->getItems() as $assetData) {
                $assets[] = $this->mediaAssetFactory->create(
                    [
                        'id' => $assetData['id'],
                        'path' => $assetData['path'],
                        'title' => $assetData['title'],
                        'description' => $assetData['description'],
                        'source' => $assetData['source'],
                        'hash' => $assetData['hash'],
                        'contentType' => $assetData['content_type'],
                        'width' => $assetData['width'],
                        'height' => $assetData['height'],
                        'size' => $assetData['size'],
                        'createdAt' => $assetData['created_at'],
                        'updatedAt' => $assetData['updated_at'],
                    ]
                );
            }
        } catch (\Exception $exception) {
            $this->logger->critical($exception);
            throw new LocalizedException(__('Could not retrieve media assets'), $exception->getMessage());
        }
        return $assets;
    }

    /**
     * Retrieve assets data from database
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return SearchResultInterface
     */
    private function getAssetsData(SearchCriteriaInterface $searchCriteria): SearchResultInterface
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
                $select->limit($searchCriteria->getPageSize(), $searchCriteria->getCurrentPage());
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
                $resourceConnection->quoteIdentifier($value),
                $condition
            );
        }
        return $resultCondition;
    }
}
