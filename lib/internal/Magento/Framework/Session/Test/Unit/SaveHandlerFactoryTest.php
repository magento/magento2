<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Session\Test\Unit;

use \Magento\Framework\Session\SaveHandlerFactory;

class SaveHandlerFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider createDataProvider
     */
    public function testCreate($handlers, $saveClass, $saveMethod)
    {
        $saveHandler = $this->getMock($saveClass);
        $objectManager = $this->getMock(
            '\Magento\Framework\ObjectManager\ObjectManager',
            ['create'],
            [],
            '',
            false
        );
        $objectManager->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            $this->equalTo($saveClass),
            $this->equalTo([])
        )->will(
            $this->returnValue($saveHandler)
        );
        $model = new SaveHandlerFactory($objectManager, $handlers);
        $result = $model->create($saveMethod);
        $this->assertInstanceOf($saveClass, $result);
        $this->assertInstanceOf('\Magento\Framework\Session\SaveHandler\Native', $result);
        $this->assertInstanceOf('\SessionHandlerInterface', $result);
    }

    /**
     * @return array
     */
    public function createDataProvider()
    {
        return [[[], 'Magento\Framework\Session\SaveHandler\Native', 'files']];
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Magento\Framework\Session\SaveHandler\Native doesn't implement \SessionHandlerInterface
     */
    public function testCreateInvalid()
    {
        $invalidSaveHandler = new \Magento\Framework\DataObject();
        $objectManager = $this->getMockBuilder('Magento\Framework\ObjectManager\ObjectManager')
            ->disableOriginalConstructor()
            ->getMock();
        $objectManager->expects($this->once())
            ->method('create')
            ->willReturn($invalidSaveHandler);
        $model = new SaveHandlerFactory($objectManager, []);
        $model->create('files');
    }
}
