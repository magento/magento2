<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DB\Select;

use Magento\Framework\DB\Select;

/**
 * Class WhereRenderer
 * @since 2.1.0
 */
class WhereRenderer implements RendererInterface
{
    /**
     * Render WHERE section
     *
     * @param Select $select
     * @param string $sql
     * @return string
     * @since 2.1.0
     */
    public function render(Select $select, $sql = '')
    {
        if ($select->getPart(Select::FROM) && $select->getPart(Select::WHERE)) {
            $sql .= ' ' . Select::SQL_WHERE . ' ' .  implode(' ', $select->getPart(Select::WHERE));
        }
        return $sql;
    }
}
