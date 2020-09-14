<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\ResourceModel\Category\Collection;

use Magento\Catalog\Model\ResourceModel\Category\Collection;
use Magento\Catalog\Model\ResourceModel\Category\Collection\Factory;
use Magento\Framework\ObjectManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FactoryTest extends TestCase
{
    /**
     * @var Factory
     */
    protected $_model;

    /**
     * @var MockObject
     */
    protected $_objectManager;

    protected function setUp(): void
    {
        $this->_objectManager = $this->getMockForAbstractClass(ObjectManagerInterface::class);
        $this->_model = new Factory($this->_objectManager);
    }

    public function testCreate()
    {
        $objectOne = $this->createMock(Collection::class);
        $objectTwo = $this->createMock(Collection::class);
        $this->_objectManager->expects(
            $this->exactly(2)
        )->method(
            'create'
        )->with(
            Collection::class,
            []
        )->will(
            $this->onConsecutiveCalls($objectOne, $objectTwo)
        );
        $this->assertSame($objectOne, $this->_model->create());
        $this->assertSame($objectTwo, $this->_model->create());
    }
}
