<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Data\Test\Unit;

use \Magento\Framework\Data\FormFactory;

/**
 * Tests for \Magento\Framework\Data\FormFactory
 */
class FormFactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_objectManagerMock;

    protected function setUp(): void
    {
        $this->_objectManagerMock = $this->createMock(\Magento\Framework\ObjectManager\ObjectManager::class);
    }

    /**
     */
    public function testWrongTypeException()
    {
        $this->expectException(\Magento\Framework\Exception\LocalizedException::class);
        $this->expectExceptionMessage('WrongClass doesn\'t extend \\Magento\\Framework\\Data\\Form');

        $formMock = $this->getMockBuilder('WrongClass')->getMock();
        $this->_objectManagerMock->expects($this->once())->method('create')->willReturn($formMock);

        $formFactory = new FormFactory($this->_objectManagerMock, 'WrongClass');
        $formFactory->create();
    }

    public function testCreate()
    {
        $className = \Magento\Framework\Data\Form::class;
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
