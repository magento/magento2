<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Unit\Model\ResourceModel\Layer\Filter;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class AttributeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Layer\Filter\Price
     */
    private $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $idxFrontendResourceMock;

    protected function setUp()
    {
        $objectManagerHelper = new ObjectManager($this);
        $this->idxFrontendResourceMock =
            $this->getMockBuilder(\Magento\Indexer\Model\ResourceModel\FrontendResource::class)
                ->disableOriginalConstructor()
                ->getMock();
        $this->model = $objectManagerHelper->getObject(
            \Magento\Catalog\Model\ResourceModel\Layer\Filter\Attribute::class,
            [
                'frontendResource' => $this->idxFrontendResourceMock
            ]
        );
    }

    public function testGetMainTable()
    {
        $expectedTableName = 'expectedTableName';
        $this->idxFrontendResourceMock->expects($this->once())->method('getMainTable')->willReturn($expectedTableName);
        $this->assertEquals($expectedTableName, $this->model->getMainTable());
    }
}
