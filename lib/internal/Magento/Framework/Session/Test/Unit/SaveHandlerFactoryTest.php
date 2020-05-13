<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Session\Test\Unit;

use Magento\Framework\DataObject;
use Magento\Framework\ObjectManager\ObjectManager;
use Magento\Framework\Session\SaveHandler\Native;
use Magento\Framework\Session\SaveHandlerFactory;
use PHPUnit\Framework\TestCase;

class SaveHandlerFactoryTest extends TestCase
{
    /**
     * @dataProvider createDataProvider
     */
    public function testCreate($handlers, $saveClass, $saveMethod)
    {
        $saveHandler = $this->createMock($saveClass);
        $objectManager = $this->createPartialMock(ObjectManager::class, ['create']);
        $objectManager->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            $saveClass,
            []
        )->willReturn(
            $saveHandler
        );
        $model = new SaveHandlerFactory($objectManager, $handlers);
        $result = $model->create($saveMethod);
        $this->assertInstanceOf($saveClass, $result);
        $this->assertInstanceOf(Native::class, $result);
        $this->assertInstanceOf('\SessionHandlerInterface', $result);
    }

    /**
     * @return array
     */
    public function createDataProvider()
    {
        return [[[], Native::class, 'files']];
    }

    public function testCreateInvalid()
    {
        $this->expectException('LogicException');
        $this->expectExceptionMessage(
            'Magento\Framework\Session\SaveHandler\Native doesn\'t implement \SessionHandlerInterface'
        );
        $invalidSaveHandler = new DataObject();
        $objectManager = $this->getMockBuilder(ObjectManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $objectManager->expects($this->once())
            ->method('create')
            ->willReturn($invalidSaveHandler);
        $model = new SaveHandlerFactory($objectManager, []);
        $model->create('files');
    }
}
