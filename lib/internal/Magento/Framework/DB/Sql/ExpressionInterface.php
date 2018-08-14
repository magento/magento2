<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DB\Sql;

/**
 * Interface ExpressionInterface
 *
 * Defines interface was implemented in Zend_Db_Expr.
 * Interface for SQL Expressions for DB Adapter/Select.
 * By using this interface a developer can strictly control type for code that manages an Expression directly.
 */
interface ExpressionInterface
{
    /**
     * @return string The string of the SQL expression stored in this object.
     */
    public function __toString();
}
