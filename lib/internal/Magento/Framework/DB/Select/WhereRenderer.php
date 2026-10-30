<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\DB\Select;

use Magento\Framework\DB\Select;

/**
 * Class WhereRenderer
 */
class WhereRenderer implements RendererInterface
{
    /**
     * Render WHERE section
     *
     * @param Select $select
     * @param string $sql
     * @return string
     */
    public function render(Select $select, $sql = '')
    {
        if ($select->getPart(Select::FROM) && $select->getPart(Select::WHERE)) {
            $sql .= ' ' . Select::SQL_WHERE . ' ' .  implode(' ', $select->getPart(Select::WHERE));
        }
        return $sql;
    }
}
