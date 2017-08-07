<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DB\Select;

use Magento\Framework\DB\Select;

/**
 * Class HavingRenderer
 * @since 2.1.0
 */
class HavingRenderer implements RendererInterface
{
    /**
     * Render HAVING section
     *
     * @param Select $select
     * @param string $sql
     * @return string
     * @since 2.1.0
     */
    public function render(Select $select, $sql = '')
    {
        if ($select->getPart(Select::FROM) && $select->getPart(Select::HAVING)) {
            $sql .= ' ' . Select::SQL_HAVING . ' ' . implode(' ', $select->getPart(Select::HAVING));
        }
        return $sql;
    }
}
