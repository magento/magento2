<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Model\Lucene\Expression;

class Constant extends AbstractExpression
{
    /**
     * @var int|float
     */
    private $value;

    /**
     * @param int|float $value
     * @throws \InvalidArgumentException
     */
    public function __construct($value)
    {
        if (!is_numeric($value)) {
            throw new \InvalidArgumentException('Only numeric constants are allowed in Lucene expressions.');
        }

        $this->value = $value;
    }

    /**
     * @return int|float
     */
    public function getValue()
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return (string) $this->getValue();
    }
}
