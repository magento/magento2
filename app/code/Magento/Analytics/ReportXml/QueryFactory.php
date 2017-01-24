<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Analytics\ReportXml;

use Magento\Analytics\ReportXml\DB\SelectBuilderFactory;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\App\CacheInterface;

/**
 * Class QueryFactory
 *
 * Creates Query object according to configuration
 * Factory for @see \Magento\Analytics\ReportXml\Query
 */
class QueryFactory
{
    /**
     * @var Config
     */
    private $config;
    /**
     * @var SelectBuilderFactory
     */
    private $selectBuilderFactory;

    /**
     * @var DB\Assembler\AssemblerInterface[]
     */
    private $assemblers;

    /**
     * @var CacheInterface
     */
    private $queryCache;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * QueryFactory constructor.
     *
     * @param CacheInterface $queryCache
     * @param ObjectManagerInterface $objectManager
     * @param SelectBuilderFactory $selectBuilderFactory
     * @param Config $config
     * @param DB\Assembler\AssemblerInterface[] $assemblers
     */
    public function __construct(
        CacheInterface $queryCache,
        ObjectManagerInterface $objectManager,
        SelectBuilderFactory $selectBuilderFactory,
        Config $config,
        $assemblers
    ) {
        $this->config = $config;
        $this->selectBuilderFactory = $selectBuilderFactory;
        $this->assemblers = $assemblers;
        $this->queryCache = $queryCache;
        $this->objectManager = $objectManager;
    }

    private function getQueryConnection($queryConfig)
    {
        $connectionName = 'default';
        if (isset($queryConfig['connection'])) {
            $connectionName = $queryConfig['connection'];
        }
        return $connectionName;
    }

    private function constructQuery($queryName)
    {
        $queryConfig = $this->config->get($queryName);
        $selectBuilder = $this->selectBuilderFactory->create();
        $selectBuilder->setConnectionName($this->getQueryConnection($queryConfig));
        foreach ($this->assemblers as $assembler) {
            $selectBuilder = $assembler->assemble($selectBuilder, $queryConfig);
        }
        $select = $selectBuilder->create();
        return $this->objectManager->create(
            Query::class,
            [
                'queryString' => $select->assemble(),
                'connectionName' => $selectBuilder->getConnectionName(),
                'parameters' => $selectBuilder->getParams()
            ]
        );
    }

    /**
     * @param string $queryName
     *
     * @return Query
     */
    public function create($queryName)
    {
        $cached = $this->queryCache->load($queryName);
        if ($cached) {
            return $this->objectManager->create(Query::class, json_decode($cached, true));
        }
        $query = $this->constructQuery($queryName);
        $this->queryCache->save(json_encode($query), $queryName);
        return $query;
    }
}
