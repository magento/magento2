<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\ResourceModel\Category\Collection;

class FactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\Collection\Factory
     */
    protected $_model;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_objectManager;

    protected function setUp(): void
    {
        $this->_objectManager = $this->createMock(\Magento\Framework\ObjectManagerInterface::class);
        $this->_model = new \Magento\Catalog\Model\ResourceModel\Category\Collection\Factory($this->_objectManager);
    }

    public function testCreate()
    {
        $objectOne = $this->createMock(\Magento\Catalog\Model\ResourceModel\Category\Collection::class);
        $objectTwo = $this->createMock(\Magento\Catalog\Model\ResourceModel\Category\Collection::class);
        $this->_objectManager->expects(
            $this->exactly(2)
        )->method(
            'create'
        )->with(
            \Magento\Catalog\Model\ResourceModel\Category\Collection::class,
            []
        )->will(
            $this->onConsecutiveCalls($objectOne, $objectTwo)
        );
        $this->assertSame($objectOne, $this->_model->create());
        $this->assertSame($objectTwo, $this->_model->create());
    }
}
