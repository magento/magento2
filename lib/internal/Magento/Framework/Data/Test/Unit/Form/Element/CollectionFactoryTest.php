<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

/**
 * Tests for \Magento\Framework\Data\Form\Element\CollectionFactory
 */
namespace Magento\Framework\Data\Test\Unit\Form\Element;

use Magento\Framework\Data\Form\Element\Collection;
use Magento\Framework\Data\Form\Element\CollectionFactory;
use Magento\Framework\ObjectManager\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CollectionFactoryTest extends TestCase
{
    /**
     * @var MockObject
     */
    protected $_objectManagerMock;

    /**
     * @var CollectionFactory
     */
    protected $_model;

    protected function setUp(): void
    {
        $objectManagerMock =
            $this->createPartialMock(ObjectManager::class, ['create']);
        $collectionMock = $this->createMock(Collection::class);
        $objectManagerMock->expects($this->once())->method('create')->willReturn($collectionMock);
        $this->_model = new CollectionFactory($objectManagerMock);
    }

    /**
     * @covers \Magento\Framework\Data\Form\Element\CollectionFactory::create
     */
    public function testCreate()
    {
        $this->assertInstanceOf(Collection::class, $this->_model->create([]));
    }
}
