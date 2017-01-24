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
        foreach ($queryConfig['source']['link-source'] as $join) {
            $joins[$this->nameResolver->getAlias($join)]  = [
                'link-type' => isset($join['link-type']) ? $join['link-type'] : 'left',
                'table' => [
                    $this->nameResolver->getAlias($join) => $this->nameResolver->getName($join)
                ],
                'condition' => $this->conditionResolver->getFilter(
                    $selectBuilder,
                    $join['using'],
                    $this->nameResolver->getAlias($join),
                    $this->nameResolver->getAlias($queryConfig['source'])
                )
            ];
            if (isset($join['filter'])) {
                $filters = array_merge(
                    $filters,
                    [
                        $this->conditionResolver->getFilter(
                            $selectBuilder,
                            $join['filter'],
                            $this->nameResolver->getAlias($join),
                            $this->nameResolver->getAlias($queryConfig['source'])
                        )
                    ]
                );
            }
            $columns = $this->columnsResolver->getColumns(
                $selectBuilder,
                isset($join['attribute']) ? $join['attribute'] : []
            );
            $selectBuilder->setColumns(array_merge($selectBuilder->getColumns(), $columns));
        }
        $selectBuilder->setFilters($filters);
        $selectBuilder->setJoins(array_merge($selectBuilder->getJoins(), $joins));
        return $selectBuilder;
    }
}
