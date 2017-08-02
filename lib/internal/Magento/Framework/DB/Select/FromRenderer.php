<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DB\Select;

use Magento\Framework\DB\Select;
use Magento\Framework\DB\Platform\Quote;

/**
 * Class \Magento\Framework\DB\Select\FromRenderer
 *
 * @since 2.1.0
 */
class FromRenderer implements RendererInterface
{
    /**
     * @var Quote
     * @since 2.1.0
     */
    protected $quote;

    /**
     * @param Quote $quote
     * @since 2.1.0
     */
    public function __construct(
        Quote $quote
    ) {
        $this->quote = $quote;
    }

    /**
     * Render FROM & JOIN's section
     *
     * @param Select $select
     * @param string $sql
     * @return string
     * @throws \Zend_Db_Select_Exception
     * @since 2.1.0
     */
    public function render(Select $select, $sql = '')
    {
        /*
         * If no table specified, use RDBMS-dependent solution
         * for table-less query.  e.g. DUAL in Oracle.
         */
        $source = $select->getPart(Select::FROM);
        if (empty($source)) {
            $source = [];
        }
        $from = [];
        foreach ($source as $correlationName => $table) {
            $tmp = '';
            $joinType = ($table['joinType'] == Select::FROM) ? Select::INNER_JOIN : $table['joinType'];
            // Add join clause (if applicable)
            if (!empty($from)) {
                $tmp .= ' ' . strtoupper($joinType) . ' ';
            }
            $tmp .= $this->getQuotedSchema($table['schema']);
            $tmp .= $this->getQuotedTable($table['tableName'], $correlationName);

            // Add join conditions (if applicable)
            if (!empty($from) && !empty($table['joinCondition'])) {
                $tmp .= ' ' . Select::SQL_ON . ' ' . $table['joinCondition'];
            }
            // Add the table name and condition add to the list
            $from[] = $tmp;
        }
        // Add the list of all joins
        if (!empty($from)) {
            $sql .= ' ' . Select::SQL_FROM . ' ' . implode("\n", $from);
        }
        return $sql;
    }

    /**
     * Return a quoted schema name
     *
     * @param string   $schema  The schema name OPTIONAL
     * @return string|null
     * @since 2.1.0
     */
    protected function getQuotedSchema($schema = null)
    {
        if ($schema === null) {
            return null;
        }
        return $this->quote->quoteIdentifier($schema) . '.';
    }

    /**
     * Return a quoted table name
     *
     * @param string   $tableName        The table name
     * @param string   $correlationName  The correlation name OPTIONAL
     * @return string
     * @since 2.1.0
     */
    protected function getQuotedTable($tableName, $correlationName = null)
    {
        return $this->quote->quoteTableAs($tableName, $correlationName);
    }
}
