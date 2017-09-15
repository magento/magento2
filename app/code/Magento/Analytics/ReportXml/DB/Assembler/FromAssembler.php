<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Analytics\ReportXml\DB\Assembler;

use Magento\Analytics\ReportXml\DB\ColumnsResolver;
use Magento\Analytics\ReportXml\DB\SelectBuilder;
use Magento\Analytics\ReportXml\DB\NameResolver;
use Magento\Framework\App\ResourceConnection;

/**
 * Assembles FROM condition
 * @since 2.2.0
 */
class FromAssembler implements AssemblerInterface
{
    /**
     * @var NameResolver
     * @since 2.2.0
     */
    private $nameResolver;

    /**
     * @var ColumnsResolver
     * @since 2.2.0
     */
    private $columnsResolver;

    /**
     * @var ResourceConnection
     * @since 2.2.0
     */
    private $resourceConnection;

    /**
     * @param NameResolver $nameResolver
     * @param ColumnsResolver $columnsResolver
     * @param ResourceConnection $resourceConnection
     * @since 2.2.0
     */
    public function __construct(
        NameResolver $nameResolver,
        ColumnsResolver $columnsResolver,
        ResourceConnection $resourceConnection
    ) {
        $this->nameResolver = $nameResolver;
        $this->columnsResolver = $columnsResolver;
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * Assembles FROM condition
     *
     * @param SelectBuilder $selectBuilder
     * @param array $queryConfig
     * @return SelectBuilder
     * @since 2.2.0
     */
    public function assemble(SelectBuilder $selectBuilder, $queryConfig)
    {
        $selectBuilder->setFrom(
            [
                $this->nameResolver->getAlias($queryConfig['source']) =>
                    $this->resourceConnection
                        ->getTableName($this->nameResolver->getName($queryConfig['source'])),
            ]
        );
        $columns = $this->columnsResolver->getColumns($selectBuilder, $queryConfig['source']);
        $selectBuilder->setColumns(array_merge($selectBuilder->getColumns(), $columns));
        return $selectBuilder;
    }
}
