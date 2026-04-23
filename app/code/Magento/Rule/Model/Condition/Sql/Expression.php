<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */

namespace Magento\Rule\Model\Condition\Sql;

/**
 * Abstract Rule sql condition
 */
class Expression extends \Zend_Db_Expr
{
    /**
     * Turn expression in this object into string
     *
     * @return string
     */
    public function __toString()
    {
        return empty($this->_expression) ? '' : '(' . $this->_expression . ')';
    }
}
