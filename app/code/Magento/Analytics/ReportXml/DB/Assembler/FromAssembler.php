<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Analytics\ReportXml\DB\Assembler;

use Magento\Analytics\ReportXml\DB\ColumnsResolver;
use Magento\Analytics\ReportXml\DB\SelectBuilder;
use Magento\Analytics\ReportXml\DB\NameResolver;

/**
 * Class FromAssembler
 *
 * Assembles FROM condition
 */
class FromAssembler implements AssemblerInterface
{
    /**
     * @var NameResolver
     */
    private $nameResolver;

    /**
     * @var ColumnsResolver
     */
    private $columnsResolver;

    /**
     * FromAssembler constructor.
     *
     * @param NameResolver $nameResolver
     * @param ColumnsResolver $columnsResolver
     */
    public function __construct(
        NameResolver $nameResolver,
        ColumnsResolver $columnsResolver
    ) {
        $this->nameResolver = $nameResolver;
        $this->columnsResolver = $columnsResolver;
    }

    /**
     * Assembles FROM condition
     *
     * @param SelectBuilder $selectBuilder
     * @param array $queryConfig
     * @return SelectBuilder
     */
    public function assemble(SelectBuilder $selectBuilder, $queryConfig)
    {
        $selectBuilder->setFrom(
            [
                $this->nameResolver->getAlias($queryConfig['source'])
                    => $this->nameResolver->getName($queryConfig['source'])
            ]
        );
        $columns = $this->columnsResolver->getColumns($selectBuilder, $queryConfig['source']);
        $selectBuilder->setColumns(array_merge($selectBuilder->getColumns(), $columns));
        return $selectBuilder;
    }
}