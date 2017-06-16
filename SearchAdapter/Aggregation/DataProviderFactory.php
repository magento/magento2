<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Elasticsearch\SearchAdapter\Aggregation;

use Magento\Elasticsearch\SearchAdapter\QueryAwareInterface;
use Magento\Elasticsearch\SearchAdapter\QueryContainer;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Search\Dynamic\DataProviderInterface;

/**
 * It's a factory which allows to override instance of DataProviderInterface
 * with the instance of the same class but with injected search query.
 */
class DataProviderFactory
{
    /**
     * Object Manager
     *
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Recreates an instance of the DataProviderInterface in order to support QueryContainer interface
     * and add a QueryContainer to the DataProvider
     *
     * IMPORTANT: This code will not work correctly with virtual types,
     * so please avoid usage of virtual types and QueryAwareInterface for the same object.
     *
     * This happens as virtual type ain't create its own class,
     * and therefore get_class will return a name of the origin class
     * which will be recreated with its default configuration.
     *
     * @param DataProviderInterface $dataProvider
     * @param QueryContainer $query
     * @return DataProviderInterface
     */
    public function create(DataProviderInterface $dataProvider, QueryContainer $query)
    {
        $result = $dataProvider;
        if ($dataProvider instanceof QueryAwareInterface) {
            $className = get_class($dataProvider);
            $result = $this->objectManager->create($className, ['queryContainer' => $query]);
        }

        return $result;
    }
}
