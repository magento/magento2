<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AdvancedSearch\Model;

use Magento\Framework\ObjectManagerInterface;
use Magento\Search\Model\QueryInterface;
use Magento\Search\Model\EngineResolver;

/**
 * Class \Magento\AdvancedSearch\Model\SuggestedQueries
 *
 */
class SuggestedQueries implements SuggestedQueriesInterface
{
    /**
     * @var EngineResolver
     * @since 2.1.0
     */
    private $engineResolver;

    /**
     * @var ObjectManagerInterface
     * @since 2.1.0
     */
    private $objectManager;

    /**
     * Array of SuggestedQueriesInterface class names.
     *
     * @var array
     * @since 2.1.0
     */
    private $data;

    /**
     * @var SuggestedQueriesInterface
     * @since 2.1.0
     */
    private $dataProvider;

    /**
     * SuggestedQueries constructor.
     *
     * @param EngineResolver $engineResolver
     * @param ObjectManagerInterface $objectManager
     * @param array $data
     * @since 2.1.0
     */
    public function __construct(EngineResolver $engineResolver, ObjectManagerInterface $objectManager, array $data)
    {
        $this->engineResolver = $engineResolver;
        $this->objectManager = $objectManager;
        $this->data = $data;
    }

    /**
     * {@inheritdoc}
     */
    public function isResultsCountEnabled()
    {
        return $this->getDataProvider()->isResultsCountEnabled();
    }

    /**
     * {@inheritdoc}
     */
    public function getItems(QueryInterface $query)
    {
        return $this->getDataProvider()->getItems($query);
    }

    /**
     * Returns DataProvider for SuggestedQueries
     *
     * @return SuggestedQueriesInterface|SuggestedQueriesInterface[]
     * @throws \Exception
     * @since 2.1.0
     */
    private function getDataProvider()
    {
        if (empty($this->dataProvider)) {
            $currentEngine = $this->engineResolver->getCurrentSearchEngine();
            $this->dataProvider = $this->objectManager->create($this->data[$currentEngine]);
            if (!$this->dataProvider instanceof SuggestedQueriesInterface) {
                throw new \InvalidArgumentException(
                    'Data provider must implement \Magento\AdvancedSearch\Model\SuggestedQueriesInterface'
                );
            }
        }
        return $this->dataProvider;
    }
}
