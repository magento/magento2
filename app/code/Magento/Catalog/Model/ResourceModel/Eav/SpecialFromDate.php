<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\ResourceModel\Eav;

class SpecialFromDate extends Attribute
{
    /**
     * @inheritDoc
     */
    public function isAllowedEmptyTextValue($value)
    {
        return !in_array($value, [self::EMPTY_STRING, null, false], true);
    }
}
