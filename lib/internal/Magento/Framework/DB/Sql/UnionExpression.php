<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DB\Sql;

use Magento\Framework\DB\Select;

class UnionExpression extends \Zend_Db_Expr
{
    /**
     * @var Select[]
     */
    protected $parts;

    /**
     * @var string
     */
    protected $type;

    /**
     * @param Select[] $parts
     * @param string $type
     */
    public function __construct(array $parts, $type = Select::SQL_UNION)
    {
        $this->parts = $parts;
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $parts = [];
        foreach ($this->parts as $part) {
            if ($part instanceof Select) {
                $parts[] = sprintf('(%s)', $part->assemble());
            } else {
                $parts[] = $part;
            }
        }
        return implode($parts, $this->type);
    }
}
