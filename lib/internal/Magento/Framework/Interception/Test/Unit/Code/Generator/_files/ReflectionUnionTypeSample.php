<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
