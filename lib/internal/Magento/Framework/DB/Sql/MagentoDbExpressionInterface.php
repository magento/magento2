<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DB\Sql;

use Zend\Stdlib\JsonSerializable;

/**
 * Interface MagentoDbExpressionInterface
 *
 * Defines interface was implemented in Zend_Db_Expr.
 */
interface MagentoDbExpressionInterface extends JsonSerializable
{
    /**
     * @inheritdoc
     */
    public function __toString();
}
