<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DB\Select;

use Magento\Framework\DB\Select;
use Magento\Framework\DB\Platform\Quote;

/**
 * Class OrderRenderer
 */
class OrderRenderer implements RendererInterface
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
     * Render ORDER BY section
     *
     * @param Select $select
     * @param string $sql
     * @return string
     */
    public function render(Select $select, $sql = '')
    {
        if ($select->getPart(Select::ORDER)) {
            $order = [];
            foreach ($select->getPart(Select::ORDER) as $term) {
                if (is_array($term)) {
                    if (is_numeric($term[0]) && (string)(int)$term[0] == $term[0]) {
                        $order[] = (int)trim($term[0]) . ' ' . $term[1];
                    } else {
                        $order[] = $this->quote->quoteIdentifier($term[0]) . ' ' . $term[1];
                    }
                } elseif (is_numeric($term) && (string)(int)$term == $term) {
                    $order[] = (int)trim($term);
                } else {
                    $order[] = $this->quote->quoteIdentifier($term);
                }
            }
            $sql .= ' ' . Select::SQL_ORDER_BY . ' ' . implode(', ', $order) . PHP_EOL;
        }
        return $sql;
    }
}
