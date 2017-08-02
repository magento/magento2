<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DB\Select;

use Magento\Framework\DB\Select;
use Magento\Framework\DB\Sql\LimitExpression;

/**
 * Class LimitRenderer
 * @since 2.1.0
 */
class LimitRenderer implements RendererInterface
{
    /**
     * Render LIMIT section
     *
     * @param Select $select
     * @param string $sql
     * @return LimitExpression|string
     * @since 2.1.0
     */
    public function render(Select $select, $sql = '')
    {
        $count = 0;
        $offset = 0;
        if (!empty($select->getPart(Select::LIMIT_OFFSET))) {
            $offset = (int) $select->getPart(Select::LIMIT_OFFSET);
            $count = PHP_INT_MAX;
        }
        if (!empty($select->getPart(Select::LIMIT_COUNT))) {
            $count = (int) $select->getPart(Select::LIMIT_COUNT);
        }
        /*
         * Add limits clause
         */
        if ($count > 0) {
            $sql = new LimitExpression($sql, $count, $offset);
        }
        return $sql;
    }
}
