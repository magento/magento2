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
 * @since 2.2.0
 */
interface ExpressionInterface
{
    /**
     * @return string The string of the SQL expression stored in this object.
     * @since 2.2.0
     */
    public function __toString();
}
