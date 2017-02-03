<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Analytics\ReportXml\DB\Assembler;

use Magento\Analytics\ReportXml\DB\NameResolver;
use Magento\Analytics\ReportXml\DB\SelectBuilder;
use Magento\Analytics\ReportXml\DB\ConditionResolver;
use Magento\Analytics\ReportXml\DB\ColumnsResolver;

/**
 * Class JoinAssembler
 *
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
     * JoinAssembler constructor.
     *
     * @param ConditionResolver $conditionResolver
     * @param ColumnsResolver $columnsResolver
     * @param NameResolver $nameResolver
     */
    public function __construct(
        ConditionResolver $conditionResolver,
        ColumnsResolver $columnsResolver,
        NameResolver $nameResolver
    ) {
        $this->conditionResolver = $conditionResolver;
        $this->nameResolver = $nameResolver;
        $this->columnsResolver = $columnsResolver;
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
                    $joinAlias => $this->nameResolver->getName($join)
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
