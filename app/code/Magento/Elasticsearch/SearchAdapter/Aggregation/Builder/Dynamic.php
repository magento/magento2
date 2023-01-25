<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\SearchAdapter\Aggregation\Builder;

use Magento\Framework\Search\Dynamic\DataProviderInterface;
use Magento\Framework\Search\Dynamic\Algorithm\Repository;
use Magento\Framework\Search\Dynamic\EntityStorage;
use Magento\Framework\Search\Dynamic\EntityStorageFactory;
use Magento\Framework\Search\Request\Aggregation\DynamicBucket;
use Magento\Framework\Search\Request\BucketInterface as RequestBucketInterface;

class Dynamic implements BucketBuilderInterface
{
    /**
     * @var Repository
     */
    private $algorithmRepository;

    /**
     * @var EntityStorageFactory
     */
    private $entityStorageFactory;

    /**
     * @param Repository $algorithmRepository
     * @param EntityStorageFactory $entityStorageFactory
     */
    public function __construct(Repository $algorithmRepository, EntityStorageFactory $entityStorageFactory)
    {
        $this->algorithmRepository = $algorithmRepository;
        $this->entityStorageFactory = $entityStorageFactory;
    }

    /**
     * @inheritdoc
     */
    public function build(
        RequestBucketInterface $bucket,
        array $dimensions,
        array $queryResult,
        DataProviderInterface $dataProvider
    ) {
        /** @var DynamicBucket $bucket */
        $algorithm = $this->algorithmRepository->get($bucket->getMethod(), ['dataProvider' => $dataProvider]);
        $data = $algorithm->getItems($bucket, $dimensions, $this->getEntityStorage($queryResult));
        return $this->prepareData($data);
    }

    /**
     * Extract Document ids
     *
     * @param array $queryResult
     * @return EntityStorage
     */
    private function getEntityStorage(array $queryResult)
    {
        $ids = [];
        foreach ($queryResult['hits']['hits'] as $document) {
            if (!array_key_exists('_id', $document) && isset($document['fields']['_id'][0])) {
                $document['_id'] = $document['fields']['_id'][0];
                unset($document['fields']);
            }
            $ids[] = $document['_id'];
        }

        return $this->entityStorageFactory->create($ids);
    }

    /**
     * Prepare result data
     *
     * @param array $data
     * @return array
     */
    private function prepareData($data)
    {
        $resultData = [];
        foreach ($data as $value) {
            $rangeName = "{$value['from']}_{$value['to']}";
            $value['value'] = $rangeName;
            $resultData[$rangeName] = $value;
        }

        return $resultData;
    }
}
