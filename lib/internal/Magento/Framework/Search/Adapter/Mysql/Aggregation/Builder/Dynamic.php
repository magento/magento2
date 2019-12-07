<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Adapter\Mysql\Aggregation\Builder;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Search\Adapter\Mysql\Aggregation\DataProviderInterface;
use Magento\Framework\Search\Dynamic\Algorithm\Repository;
use Magento\Framework\Search\Dynamic\EntityStorageFactory;
use Magento\Framework\Search\Request\Aggregation\DynamicBucket;
use Magento\Framework\Search\Request\BucketInterface as RequestBucketInterface;

/**
 * MySQL search dynamic aggregation builder.
 *
 * @deprecated
 * @see \Magento\ElasticSearch
 */
class Dynamic implements BucketInterface
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
    public function __construct(
        Repository $algorithmRepository,
        EntityStorageFactory $entityStorageFactory
    ) {
        $this->algorithmRepository = $algorithmRepository;
        $this->entityStorageFactory = $entityStorageFactory;
    }

    /**
     * @inheritdoc
     */
    public function build(
        DataProviderInterface $dataProvider,
        array $dimensions,
        RequestBucketInterface $bucket,
        Table $entityIdsTable
    ) {
        /** @var DynamicBucket $bucket */
        $algorithm = $this->algorithmRepository->get($bucket->getMethod());
        $data = $algorithm->getItems($bucket, $dimensions, $this->entityStorageFactory->create($entityIdsTable));

        $resultData = $this->prepareData($data);

        return $resultData;
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
            $from = is_numeric($value['from']) ? $value['from'] : '*';
            $to = is_numeric($value['to']) ? $value['to'] : '*';
            unset($value['from'], $value['to']);

            $rangeName = "{$from}_{$to}";
            $resultData[$rangeName] = array_merge(['value' => $rangeName], $value);
        }

        return $resultData;
    }
}
