<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Tests for \Magento\Framework\Data\Form\Element\CollectionFactory
 */
namespace Magento\Framework\Data\Test\Unit\Form\Element;

class CollectionFactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_objectManagerMock;

    /**
     * @var \Magento\Framework\Data\Form\Element\CollectionFactory
     */
    protected $_model;

    protected function setUp(): void
    {
        $objectManagerMock =
            $this->createPartialMock(\Magento\Framework\ObjectManager\ObjectManager::class, ['create']);
        $collectionMock = $this->createMock(\Magento\Framework\Data\Form\Element\Collection::class);
        $objectManagerMock->expects($this->once())->method('create')->willReturn($collectionMock);
        $this->_model = new \Magento\Framework\Data\Form\Element\CollectionFactory($objectManagerMock);
    }

    /**
     * @covers \Magento\Framework\Data\Form\Element\CollectionFactory::create
     */
    public function testCreate()
    {
        $this->assertInstanceOf(\Magento\Framework\Data\Form\Element\Collection::class, $this->_model->create([]));
    }
}
