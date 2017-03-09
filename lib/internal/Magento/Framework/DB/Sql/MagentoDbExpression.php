<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DB\Sql;

/**
 * Class is wrapper over Zend_Db_Expr for implement JsonSerializable interface.
 */
class MagentoDbExpression extends \Zend_Db_Expr implements MagentoDbExpressionInterface
{
    /**
     * @inheritdoc
     */
    public function jsonSerialize()
    {
        return [
            'class' => static::class,
            'arguments' => [
                'expression' => $this->_expression,
            ],
        ];
    }
}
