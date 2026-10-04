<?php
/**
 * Copyright 2022 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Interception\Code\Generator;

use Magento\Backend\Model\Menu;

class ReflectionIntersectionTypeSample extends Menu
{
    /**
     * Intersection type attribute
     *
     * @var ReflectionIntersectionTypeSample&Menu
     */
    private ReflectionIntersectionTypeSample&Menu $attribute;

    /**
     * @return ReflectionIntersectionTypeSample&Menu
     */
    public function getValue(): ReflectionIntersectionTypeSample&Menu
    {
        return $this->attribute;
    }

    /**
     * @param ReflectionIntersectionTypeSample&Menu $value
     */
    public function setValue(ReflectionIntersectionTypeSample&Menu $value)
    {
        $this->attribute = $value;
    }
}
