<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\ReportXml\DB\Assembler;

use Magento\Analytics\ReportXml\DB\SelectBuilder;

/**
 * Interface AssemblerInterface
 *
 * Introduces family of SQL assemblers
 * Each assembler populates SelectBuilder with config information
 * @see usage examples at \Magento\Analytics\ReportXml\QueryFactory
 */
interface AssemblerInterface
{
    /**
     * Assemble SQL statement
     *
     * @param SelectBuilder $selectBuilder
     * @param array $queryConfig
     * @return SelectBuilder
     */
    public function assemble(SelectBuilder $selectBuilder, $queryConfig);
}
