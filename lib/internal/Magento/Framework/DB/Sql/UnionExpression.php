<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DB\Sql;

use Magento\Framework\DB\Select;

/**
 * Class UnionExpression
 */
class UnionExpression extends Expression
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
     * @var string
     */
    protected $pattern;

    /**
     * @param Select[] $parts
     * @param string $type (optional)
     * @param string $pattern (optional)
     */
    public function __construct(array $parts, $type = Select::SQL_UNION, $pattern = '')
    {
        $this->parts = $parts;
        $this->type = $type;
        $this->pattern = $pattern;
    }

    /**
     * @inheritdoc
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
        $sql = implode($parts, $this->type);
        if ($this->pattern) {
            return sprintf($this->pattern, $sql);
        }
        return $sql;
    }
}
