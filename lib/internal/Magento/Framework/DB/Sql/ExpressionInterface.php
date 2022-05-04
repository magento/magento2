<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\DB\Sql;

/**
 * Interface ExpressionInterface
 *
 * Defines interface was implemented in Zend_Db_Expr.
 * Interface for SQL Expressions for DB Adapter/Select.
 * By using this interface a developer can strictly control type for code that manages an Expression directly.
 *
 * @api
 */
interface ExpressionInterface
{
    /**
     * The string of the SQL expression stored in this object.
     *
     * @return string
     */
    public function __toString();
}
