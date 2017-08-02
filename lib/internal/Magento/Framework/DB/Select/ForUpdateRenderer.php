<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DB\Select;

use Magento\Framework\DB\Select;

/**
 * Class ForUpdateRenderer
 * @since 2.1.0
 */
class ForUpdateRenderer implements RendererInterface
{
    /**
     * Render FOR UPDATE section
     *
     * @param Select $select
     * @param string $sql
     * @return string
     * @throws \Zend_Db_Select_Exception
     * @since 2.1.0
     */
    public function render(Select $select, $sql = '')
    {
        if ($select->getPart(Select::FOR_UPDATE)) {
            $sql .= ' ' . Select::SQL_FOR_UPDATE;
        }
        return $sql;
    }
}
