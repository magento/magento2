<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Db;

use Magento\TestFramework\Db\DymanicTables\CategoryProductIndexTables;

/**
 * Class to pre-create dynamic tables
 */
class DynamicTables
{
    /**
     * @var CategoryProductIndexTables
     */
    private $categoryProductIndexTables;

    /**
     * @param CategoryProductIndexTables $categoryProductIndexTables
     */
    public function __construct(
        CategoryProductIndexTables $categoryProductIndexTables
    ) {
        $this->categoryProductIndexTables = $categoryProductIndexTables;
    }

    /**
     * Create dynamic tables before the test to preserve integration tests isolation
     */
    public function createTables()
    {
        $this->categoryProductIndexTables->createTables();
    }
}
