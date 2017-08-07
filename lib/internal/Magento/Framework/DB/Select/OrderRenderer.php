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
 * @since 2.1.0
 */
class OrderRenderer implements RendererInterface
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
     * Render ORDER BY section
     *
     * @param Select $select
     * @param string $sql
     * @return string
     * @since 2.1.0
     */
    public function render(Select $select, $sql = '')
    {
        if ($select->getPart(Select::ORDER)) {
            $order = [];
            foreach ($select->getPart(Select::ORDER) as $term) {
                if (is_array($term)) {
                    if (is_numeric($term[0]) && strval(intval($term[0])) == $term[0]) {
                        $order[] = (int)trim($term[0]) . ' ' . $term[1];
                    } else {
                        $order[] = $this->quote->quoteIdentifier($term[0]) . ' ' . $term[1];
                    }
                } elseif (is_numeric($term) && strval(intval($term)) == $term) {
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
