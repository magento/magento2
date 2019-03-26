<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Adapter\Mysql\Aggregation;

/**
 * MySQL search data provider container.
 *
 * @deprecated 102.0.0
 * @see \Magento\ElasticSearch
 * @api
 * @since 100.0.2
 */
class DataProviderContainer
{
    /**
     * @var DataProviderInterface[]
     */
    private $dataProvider;

    /**
     * @param DataProviderInterface[] $dataProviders
     */
    public function __construct(array $dataProviders)
    {
        $this->dataProvider = $dataProviders;
    }

    /**
     * Get data provider by index name.
     *
     * @param string $indexName
     * @return DataProviderInterface
     */
    public function get($indexName)
    {
        return $this->dataProvider[$indexName];
    }
}
