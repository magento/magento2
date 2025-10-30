<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Product\Compare;

use Magento\Catalog\Model\Product\Compare\Item;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

class ItemTest extends TestCase
{
    /**
     * @var Item
     */
    protected $model;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->model = $objectManager->getObject(Item::class);
    }

    protected function tearDown(): void
    {
        $this->model = null;
    }

    public function testGetIdentities()
    {
        $id = 1;
        $this->model->setId($id);
        $this->assertEquals(
            [Item::CACHE_TAG . '_' . $id],
            $this->model->getIdentities()
        );
    }
}
