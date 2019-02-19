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
     * Recreates an instance of the DataProviderInterface in order to support QueryAware interface
     * and add a QueryContainer to the DataProvider
     *
     * The Query is an optional argument as it's not required to pass the QueryContainer for data providers
     * who not implementing QueryAwareInterface, but the method is also responsible for checking
     * if the query is passed for those who expects it.
     *
     * IMPORTANT: This code will not work correctly with virtual types,
     * so please avoid usage of virtual types and QueryAwareInterface for the same object.
     *
     * This happens as virtual type ain't create its own class,
     * and therefore get_class will return a name of the original class
     * which will be recreated with its default configuration.
     *
     * @param DataProviderInterface $dataProvider
     * @param QueryContainer $query
     * @return DataProviderInterface
     * @throws \LogicException when the query is missing but it required according to the QueryAwareInterface
     */
    public function create(DataProviderInterface $dataProvider, QueryContainer $query = null)
    {
        $result = $dataProvider;
        if ($dataProvider instanceof QueryAwareInterface) {
            if (null === $query) {
                throw new \LogicException(
                    'Instance of ' . QueryAwareInterface::class . ' must be configured with a search query,'
                    . ' but the query is empty'
                );
            }

            $className = get_class($dataProvider);
            $result = $this->objectManager->create($className, ['queryContainer' => $query]);
        }

        return $result;
    }
}
