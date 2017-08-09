<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
