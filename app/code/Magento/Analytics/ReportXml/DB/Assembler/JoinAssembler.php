<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Analytics\ReportXml\DB\Assembler;

use Magento\Analytics\ReportXml\DB\NameResolver;
use Magento\Analytics\ReportXml\DB\SelectBuilder;
use Magento\Analytics\ReportXml\DB\ConditionResolver;
use Magento\Analytics\ReportXml\DB\ColumnsResolver;
use Magento\Framework\App\ResourceConnection;

/**
 * Assembles JOIN conditions
 */
class JoinAssembler implements AssemblerInterface
{
    /**
     * @var ConditionResolver
     */
    private $conditionResolver;

    /**
     * @var NameResolver
     */
    private $nameResolver;

    /**
     * @var ColumnsResolver
     */
    private $columnsResolver;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @param ConditionResolver $conditionResolver
     * @param ColumnsResolver $columnsResolver
     * @param NameResolver $nameResolver
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        ConditionResolver $conditionResolver,
        ColumnsResolver $columnsResolver,
        NameResolver $nameResolver,
        ResourceConnection $resourceConnection
    ) {
        $this->conditionResolver = $conditionResolver;
        $this->nameResolver = $nameResolver;
        $this->columnsResolver = $columnsResolver;
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * Assembles JOIN conditions
     *
     * @param SelectBuilder $selectBuilder
     * @param array $queryConfig
     * @return SelectBuilder
     */
    public function assemble(SelectBuilder $selectBuilder, $queryConfig)
    {
        if (!isset($queryConfig['source']['link-source'])) {
            return $selectBuilder;
        }
        $joins = [];
        $filters = $selectBuilder->getFilters();

        $sourceAlias = $this->nameResolver->getAlias($queryConfig['source']);

        foreach ($queryConfig['source']['link-source'] as $join) {
            $joinAlias = $this->nameResolver->getAlias($join);

            $joins[$joinAlias]  = [
                'link-type' => isset($join['link-type']) ? $join['link-type'] : 'left',
                'table' => [
                    $joinAlias => $this->resourceConnection
                        ->getTableName($this->nameResolver->getName($join)),
                ],
                'condition' => $this->conditionResolver->getFilter(
                    $selectBuilder,
                    $join['using'],
                    $joinAlias,
                    $sourceAlias
                )
            ];
            if (isset($join['filter'])) {
                $filters = array_merge(
                    $filters,
                    [
                        $this->conditionResolver->getFilter(
                            $selectBuilder,
                            $join['filter'],
                            $joinAlias,
                            $sourceAlias
                        )
                    ]
                );
            }
            $columns = $this->columnsResolver->getColumns($selectBuilder, isset($join['attribute']) ? $join : []);
            $selectBuilder->setColumns(array_merge($selectBuilder->getColumns(), $columns));
        }
        $selectBuilder->setFilters($filters);
        $selectBuilder->setJoins(array_merge($selectBuilder->getJoins(), $joins));
        return $selectBuilder;
    }
}
