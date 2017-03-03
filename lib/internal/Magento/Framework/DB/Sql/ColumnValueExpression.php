<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DB\Sql;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;

/**
 * Class Column Value Expression
 *
 * Just a wrapper over Zend_Db_Expr to eliminate direct dependency on it
 * @api
 */
class ColumnValueExpression extends \Zend_Db_Expr
{
}
