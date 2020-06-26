<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Product;

use Magento\Catalog\Model\Product\CopyConstructor\Composite;
use Magento\Catalog\Model\Product\CopyConstructorFactory;
use Magento\Framework\DataObject;
use Magento\Framework\ObjectManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CopyConstructorFactoryTest extends TestCase
{
    /**
     * @var CopyConstructorFactory
     */
    protected $_model;

    /**
     * @var MockObject
     */
    protected $_objectManagerMock;

    protected function setUp(): void
    {
        $this->_objectManagerMock = $this->getMockForAbstractClass(ObjectManagerInterface::class);
        $this->_model = new CopyConstructorFactory($this->_objectManagerMock);
    }

    public function testCreateWithInvalidType()
    {
        $this->expectException('\InvalidArgumentException');
        $this->expectExceptionMessage(
            'Magento\Framework\DataObject does not implement \Magento\Catalog\Model\Product\CopyConstructorInterface'
        );
        $this->_objectManagerMock->expects($this->never())->method('create');
        $this->_model->create(DataObject::class);
    }

    public function testCreateWithValidType()
    {
        $this->_objectManagerMock->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            Composite::class
        )->willReturn(
            'object'
        );
        $this->assertEquals(
            'object',
            $this->_model->create(Composite::class)
        );
    }
}
