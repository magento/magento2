<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Adapter\Mysql\Aggregation;

/**
 * @api
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
     * @param string $indexName
     * @return DataProviderInterface
     */
    public function get($indexName)
    {
        return $this->dataProvider[$indexName];
    }
}
