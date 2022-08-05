<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Analytics\ReportXml;

use Magento\Analytics\ReportXml\DB\SelectBuilderFactory;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Creates Query object according to configuration
 *
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
     * @var Json
     */
    private $jsonSerializer;

    /**
     * QueryFactory constructor.
     *
     * @param CacheInterface $queryCache
     * @param SelectHydrator $selectHydrator
     * @param ObjectManagerInterface $objectManager
     * @param SelectBuilderFactory $selectBuilderFactory
     * @param Config $config
     * @param array $assemblers
     * @param Json $jsonSerializer
     */
    public function __construct(
        CacheInterface $queryCache,
        SelectHydrator $selectHydrator,
        ObjectManagerInterface $objectManager,
        SelectBuilderFactory $selectBuilderFactory,
        Config $config,
        array $assemblers,
        Json $jsonSerializer
    ) {
        $this->config = $config;
        $this->selectBuilderFactory = $selectBuilderFactory;
        $this->assemblers = $assemblers;
        $this->queryCache = $queryCache;
        $this->objectManager = $objectManager;
        $this->selectHydrator = $selectHydrator;
        $this->jsonSerializer = $jsonSerializer;
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
        return $this->createQueryObject(
            $select,
            $selectBuilder->getConnectionName(),
            $queryConfig
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
            $queryData = $this->jsonSerializer->unserialize($cached);
            return $this->createQueryObject(
                $this->selectHydrator->recreate($queryData['select_parts']),
                $queryData['connectionName'],
                $queryData['config']
            );
        }
        $query = $this->constructQuery($queryName);
        $this->queryCache->save(
            $this->jsonSerializer->serialize($query),
            $queryName
        );
        return $query;
    }

    /**
     * Create query class using objectmanger
     *
     * @param Select $select
     * @param string $connection
     * @param array $queryConfig
     * @return Query
     */
    private function createQueryObject(
        Select $select,
        string $connection,
        array $queryConfig
    ) {
        return $this->objectManager->create(
            Query::class,
            [
                'select' => $select,
                'selectHydrator' => $this->selectHydrator,
                'connectionName' => $connection,
                'config' => $queryConfig
            ]
        );
    }
}
