<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Webapi\Test\Unit\Model\Config;

class PartialDocBlockServiceForClassReflector
{
    /**
     * Save items.
     *
     * @param string[] $items Items to save
     * @return bool
     */
    public function save(int $id, array $items): bool
    {
        return $id > 0 && $items !== [];
    }
}
