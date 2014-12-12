<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
