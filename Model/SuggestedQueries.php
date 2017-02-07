<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AdvancedSearch\Model;

use Magento\Framework\ObjectManagerInterface;
use Magento\Search\Model\QueryInterface;
use Magento\Search\Model\EngineResolver;

class SuggestedQueries implements SuggestedQueriesInterface
{
    /**
     * @var EngineResolver
     */
    private $engineResolver;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * Array of SuggestedQueriesInterface class names.
     *
     * @var array
     */
    private $data;

    /**
     * @var SuggestedQueriesInterface
     */
    private $dataProvider;

    /**
     * SuggestedQueries constructor.
     *
     * @param EngineResolver $engineResolver
     * @param ObjectManagerInterface $objectManager
     * @param array $data
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
