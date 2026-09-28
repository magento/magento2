<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\DB\Select;

use Magento\Framework\DB\Select;

/**
 * Class DistinctRenderer
 */
class DistinctRenderer implements RendererInterface
{
    /**
     * Render DISTINCT section
     *
     * @param Select $select
     * @param string $sql
     * @return string
     */
    public function render(Select $select, $sql = '')
    {
        if ($select->getPart(Select::DISTINCT)) {
            $sql .= ' ' . Select::SQL_DISTINCT  . ' ';
        }
        return $sql;
    }
}
