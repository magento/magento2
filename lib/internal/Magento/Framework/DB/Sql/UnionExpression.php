<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DB\Sql;

use Magento\Framework\DB\Select;

/**
 * Class UnionExpression
 * @since 2.1.0
 */
class UnionExpression extends Expression
{
    /**
     * @var Select[]
     * @since 2.1.0
     */
    protected $parts;

    /**
     * @var string
     * @since 2.1.0
     */
    protected $type;

    /**
     * @param Select[] $parts
     * @param string $type
     * @since 2.1.0
     */
    public function __construct(array $parts, $type = Select::SQL_UNION)
    {
        $this->parts = $parts;
        $this->type = $type;
    }

    /**
     * @return string
     * @since 2.1.0
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
