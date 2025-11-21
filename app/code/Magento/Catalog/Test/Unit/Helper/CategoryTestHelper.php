<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Helper;

use Magento\Catalog\Model\Category;

/**
 * Test helper class for Catalog Category with custom methods
 */
class CategoryTestHelper extends Category
{

    /**
     * Get is anchor
     *
     * @return bool
     */
    public function getIsAnchor(): bool
    {
        return $this->getData('is_anchor');
    }
}
