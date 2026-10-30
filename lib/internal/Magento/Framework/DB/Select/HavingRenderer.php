<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\DB\Select;

use Magento\Framework\DB\Select;

/**
 * Class HavingRenderer
 */
class HavingRenderer implements RendererInterface
{
    /**
     * Render HAVING section
     *
     * @param Select $select
     * @param string $sql
     * @return string
     */
    public function render(Select $select, $sql = '')
    {
        if ($select->getPart(Select::FROM) && $select->getPart(Select::HAVING)) {
            $sql .= ' ' . Select::SQL_HAVING . ' ' . implode(' ', $select->getPart(Select::HAVING));
        }
        return $sql;
    }
}
