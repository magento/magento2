<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DB\Select;

use Magento\Framework\DB\Select;
use Magento\Framework\DB\Platform\Quote;

/**
 * Class ColumnsRenderer
 */
class ColumnsRenderer implements RendererInterface
{
    /**
     * @var Quote
     */
    protected $quote;

    /**
     * @param Quote $quote
     */
    public function __construct(
        Quote $quote
    ) {
        $this->quote = $quote;
    }

    /**
     * Render COLUMNS section
     *
     * @param Select $select
     * @param string $sql
     * @return null|string
     * @throws \Zend_Db_Select_Exception
     */
    public function render(Select $select, $sql = '')
    {
        if (!count($select->getPart(Select::COLUMNS))) {
            return null;
        }
        $columns = [];
        foreach ($select->getPart(Select::COLUMNS) as $columnEntry) {
            list($correlationName, $column, $alias) = $columnEntry;
            if ($column instanceof \Zend_Db_Expr) {
                $columns[] = $this->quote->quoteColumnAs($column, $alias);
            } else {
                if ($column == Select::SQL_WILDCARD) {
                    $column = new \Zend_Db_Expr(Select::SQL_WILDCARD);
                    $alias = null;
                }
                if (empty($correlationName)) {
                    $columns[] = $this->quote->quoteColumnAs($column, $alias);
                } else {
                    $columns[] = $this->quote->quoteColumnAs([$correlationName, $column], $alias);
                }
            }
        }
        return $sql . ' ' . implode(', ', $columns);
    }
}
