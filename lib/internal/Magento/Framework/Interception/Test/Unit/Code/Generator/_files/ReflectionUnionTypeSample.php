<?php
/**
 * Copyright 2022 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Interception\Code\Generator;

class ReflectionUnionTypeSample
{
    /**
     * Union type attribute
     *
     * @var int|string
     */
    private int|string $attribute;

    public function getValue(): int|string
    {
        return $this->attribute;
    }

    /**
     * @param int|string $value
     */
    public function setValue(int|string $value)
    {
        $this->attribute = $value;
    }
}
