<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DB\Select;

use Magento\Framework\DB\Select;

/**
 * Class DistinctRenderer
 * @since 2.1.0
 */
class DistinctRenderer implements RendererInterface
{
    /**
     * Render DISTINCT section
     *
     * @param Select $select
     * @param string $sql
     * @return string
     * @since 2.1.0
     */
    public function render(Select $select, $sql = '')
    {
        if ($select->getPart(Select::DISTINCT)) {
            $sql .= ' ' . Select::SQL_DISTINCT  . ' ';
        }
        return $sql;
    }
}
