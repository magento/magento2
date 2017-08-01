<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Adapter\Mysql\Aggregation;

/**
 * @api
 * @since 2.0.0
 */
class DataProviderContainer
{
    /**
     * @var DataProviderInterface[]
     * @since 2.0.0
     */
    private $dataProvider;

    /**
     * @param DataProviderInterface[] $dataProviders
     * @since 2.0.0
     */
    public function __construct(array $dataProviders)
    {
        $this->dataProvider = $dataProviders;
    }

    /**
     * @param string $indexName
     * @return DataProviderInterface
     * @since 2.0.0
     */
    public function get($indexName)
    {
        return $this->dataProvider[$indexName];
    }
}
