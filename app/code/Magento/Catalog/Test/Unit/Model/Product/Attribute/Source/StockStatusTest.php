<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Catalog\Test\Unit\Model\Product\Attribute\Source;

use Magento\Eav\Model\Entity\AbstractEntity;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class StockStatusTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Catalog\Model\Product\Attribute\Source\StockStatus */
    protected $status;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var \Magento\Eav\Model\Entity\Collection\AbstractCollection|\PHPUnit_Framework_MockObject_MockObject */
    protected $collection;

    /** @var \Magento\Eav\Model\Entity\Attribute\AbstractAttribute|\PHPUnit_Framework_MockObject_MockObject */
    protected $attributeModel;

    /** @var \Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend|\PHPUnit_Framework_MockObject_MockObject */
    protected $backendAttributeModel;

    /**
     * @var AbstractEntity|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $entity;

    protected function setUp()
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->collection = $this->createPartialMock(\Magento\Catalog\Model\ResourceModel\Product\Collection::class, [
                '__wakeup',
                'getSelect',
                'joinLeft',
                'order',
                'getStoreId',
                'getConnection',
                'getCheckSql'
            ]);
        $this->attributeModel = $this->createPartialMock(\Magento\Catalog\Model\Entity\Attribute::class, [
                '__wakeup',
                'getAttributeCode',
                'getBackend',
                'getId',
                'isScopeGlobal',
                'getEntity',
                'getAttribute'
            ]);
        $this->backendAttributeModel = $this->createPartialMock(\Magento\Catalog\Model\Product\Attribute\Backend\Sku::class, ['__wakeup', 'getTable']);

    }

    public function testGetOptionArray()
    {
        $this->assertEquals([1 => 'In Stock', 0 => 'Out of Stock'], $this->status->getOptionArray());
    }

    /**
     * @dataProvider getOptionTextDataProvider
     * @param string $text
     * @param string $id
     */
    public function testGetOptionText($text, $id)
    {
        $this->assertEquals($text, $this->status->getOptionText($id));
    }

    /**
     * @return array
     */
    public function getOptionTextDataProvider()
    {
        return [
            [
                'text' => 'In Stock',
                'id' => '1',
            ],
            [
                'text' => 'Out of Stock',
                'id' => '0'
            ]
        ];
    }
}
