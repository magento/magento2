<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DB\Select;

use Magento\Framework\DB\Select;

/**
 * Class UseCacheRenderer
 */
class UseCacheRenderer implements RendererInterface
{
    /**
     * Render SQL_CACHE section
     *
     * @param Select $select
     * @param string $sql
     * @return string
     */
    public function render(Select $select, $sql = '')
    {
        if ($select->getPart(Select::USE_CACHE)) {
            $sql .= ' ' . Select::SQL_CACHE  . ' ';
        }
        return $sql;
    }
}
