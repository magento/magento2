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
 * @since 2.2.0
 */
class QueryFactory
{
    /**
     * @var Config
     * @since 2.2.0
     */
    private $config;

    /**
     * @var SelectBuilderFactory
     * @since 2.2.0
     */
    private $selectBuilderFactory;

    /**
     * @var DB\Assembler\AssemblerInterface[]
     * @since 2.2.0
     */
    private $assemblers;

    /**
     * @var CacheInterface
     * @since 2.2.0
     */
    private $queryCache;

    /**
     * @var ObjectManagerInterface
     * @since 2.2.0
     */
    private $objectManager;

    /**
     * @var SelectHydrator
     * @since 2.2.0
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
     * @since 2.2.0
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
     * @since 2.2.0
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
     * @since 2.2.0
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
     * @since 2.2.0
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
