<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\Product\Compare;

class ItemTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Catalog\Model\Product\Compare\Item
     */
    protected $model;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $objectManager->getObject(\Magento\Catalog\Model\Product\Compare\Item::class);
    }

    protected function tearDown()
    {
        $this->model = null;
    }

    public function testGetIdentities()
    {
        $id = 1;
        $this->model->setId($id);
        $this->assertEquals(
            [\Magento\Catalog\Model\Product\Compare\Item::CACHE_TAG . '_' . $id],
            $this->model->getIdentities()
        );
    }
}
