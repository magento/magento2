<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Adapter\Mysql\Aggregation;

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
