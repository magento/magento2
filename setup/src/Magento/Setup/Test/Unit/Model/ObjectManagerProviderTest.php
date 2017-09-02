<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Model;

use Magento\Setup\Model\ObjectManagerProvider;
use Magento\Setup\Model\Bootstrap;
use Zend\ServiceManager\ServiceLocatorInterface;
use Magento\Setup\Mvc\Bootstrap\InitParamListener;
use Magento\Framework\App\ObjectManagerFactory;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Console\CommandListInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Application;

/**
 * Class ObjectManagerProviderTest
 */
class ObjectManagerProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ServiceLocatorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $serviceLocatorMock;

    /**
     * @var Bootstrap|\PHPUnit_Framework_MockObject_MockObject
     */
    private $bootstrapMock;

    /**
     * @var ObjectManagerProvider|\PHPUnit_Framework_MockObject_MockObject
     */
    private $model;

    public function setUp()
    {
        $this->serviceLocatorMock = $this->getMock(ServiceLocatorInterface::class);
        $this->bootstrapMock = $this->getMock(Bootstrap::class);

        $this->model = new ObjectManagerProvider($this->serviceLocatorMock, $this->bootstrapMock);
    }

    public function testGet()
    {
        $initParams = ['param' => 'value'];

        $this->serviceLocatorMock
            ->expects($this->atLeastOnce())
            ->method('get')
            ->willReturnMap(
                [
                    [InitParamListener::BOOTSTRAP_PARAM, $initParams],
                    [
                        Application::class,
                        $this->getMockBuilder(Application::class)->disableOriginalConstructor()->getMock(),
                    ],
                ]
            );

        $objectManagerMock = $this->getMock(ObjectManagerInterface::class);
        $objectManagerMock->expects($this->once())
            ->method('create')
            ->with(CommandListInterface::class)
            ->willReturn($this->getCommandListMock());

        $objectManagerFactoryMock = $this->getMockBuilder(ObjectManagerFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $objectManagerFactoryMock->expects($this->once())
            ->method('create')
            ->with($initParams)
            ->willReturn($objectManagerMock);

        $this->bootstrapMock
            ->expects($this->once())
            ->method('createObjectManagerFactory')
            ->willReturn($objectManagerFactoryMock);

        $this->assertInstanceOf(ObjectManagerInterface::class, $this->model->get());
    }

    private function getCommandListMock()
    {
        $commandMock = $this->getMockBuilder(Command::class)->disableOriginalConstructor()->getMock();
        $commandMock->expects($this->once())->method('setApplication');

        $commandListMock = $this->getMock(CommandListInterface::class);
        $commandListMock->expects($this->once())
            ->method('getCommands')
            ->willReturn([$commandMock]);

        return $commandListMock;
    }
}
