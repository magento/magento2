<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\ReportXml;

use Magento\Analytics\ReportXml\DB\SelectBuilderFactory;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\ObjectManagerInterface;

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
     * @var SelectHydrator
     */
    private $selectHydrator;

    /**
     * QueryFactory constructor.
     *
     * @param CacheInterface $queryCache
     * @param SelectHydrator $selectHydrator
     * @param ObjectManagerInterface $objectManager
     * @param SelectBuilderFactory $selectBuilderFactory
     * @param Config $config
     * @param array $assemblers
     */
    public function __construct(
        CacheInterface $queryCache,
        SelectHydrator $selectHydrator,
        ObjectManagerInterface $objectManager,
        SelectBuilderFactory $selectBuilderFactory,
        Config $config,
        array $assemblers
    ) {
        $this->config = $config;
        $this->selectBuilderFactory = $selectBuilderFactory;
        $this->assemblers = $assemblers;
        $this->queryCache = $queryCache;
        $this->objectManager = $objectManager;
        $this->selectHydrator = $selectHydrator;
    }

    /**
     * Returns query connection name according to configuration
     *
     * @param string $queryConfig
     * @return string
     */
    private function getQueryConnectionName($queryConfig)
    {
        $connectionName = 'default';
        if (isset($queryConfig['connection'])) {
            $connectionName = $queryConfig['connection'];
        }
        return $connectionName;
    }

    /**
     * Create query according to configuration settings
     *
     * @param string $queryName
     * @return Query
     */
    private function constructQuery($queryName)
    {
        $queryConfig = $this->config->get($queryName);
        $selectBuilder = $this->selectBuilderFactory->create();
        $selectBuilder->setConnectionName($this->getQueryConnectionName($queryConfig));
        foreach ($this->assemblers as $assembler) {
            $selectBuilder = $assembler->assemble($selectBuilder, $queryConfig);
        }
        $select = $selectBuilder->create();
        return $this->objectManager->create(
            Query::class,
            [
                'select' => $select,
                'selectHydrator' => $this->selectHydrator,
                'connectionName' => $selectBuilder->getConnectionName(),
                'config' => $queryConfig
            ]
        );
    }

    /**
     * Creates query by name
     *
     * @param string $queryName
     * @return Query
     */
    public function create($queryName)
    {
        $cached = $this->queryCache->load($queryName);
        if ($cached) {
            $queryData = json_decode($cached, true);
            return $this->objectManager->create(
                Query::class,
                [
                    'select' => $this->selectHydrator->recreate($queryData['select_parts']),
                    'selectHydrator' => $this->selectHydrator,
                    'connectionName' => $queryData['connectionName'],
                    'config' => $queryData['config']
                ]
            );
        }
        $query = $this->constructQuery($queryName);
        $this->queryCache->save(json_encode($query), $queryName);
        return $query;
    }
}
