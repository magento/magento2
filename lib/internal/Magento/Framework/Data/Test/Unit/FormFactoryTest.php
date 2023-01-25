<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Data\Test\Unit;

use Magento\Framework\Data\Form;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\ObjectManager\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Tests for \Magento\Framework\Data\FormFactory
 */
class FormFactoryTest extends TestCase
{
    /**
     * @var MockObject
     */
    protected $_objectManagerMock;

    protected function setUp(): void
    {
        $this->_objectManagerMock = $this->createMock(ObjectManager::class);
    }

    public function testWrongTypeException()
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
        $this->expectExceptionMessage('WrongClass doesn\'t extend \Magento\Framework\Data\Form');
        $formMock = $this->getMockBuilder('WrongClass')
            ->getMock();
        $this->_objectManagerMock->expects($this->once())->method('create')->willReturn($formMock);

        $formFactory = new FormFactory($this->_objectManagerMock, 'WrongClass');
        $formFactory->create();
    }

    public function testCreate()
    {
        $className = Form::class;
        $formMock = $this->createMock($className);
        $this->_objectManagerMock->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            $className
        )->willReturn(
            $formMock
        );

        $formFactory = new FormFactory($this->_objectManagerMock, $className);
        $this->assertSame($formMock, $formFactory->create());
    }
}
