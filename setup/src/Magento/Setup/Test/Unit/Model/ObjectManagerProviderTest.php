<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
class ObjectManagerProviderTest extends \PHPUnit\Framework\TestCase
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
        $this->serviceLocatorMock = $this->createMock(ServiceLocatorInterface::class);
        $this->bootstrapMock = $this->createMock(Bootstrap::class);

        $this->model = new ObjectManagerProvider($this->serviceLocatorMock, $this->bootstrapMock);
    }

    public function testGet()
    {
        $initParams = ['param' => 'value'];
        $commands = [
            new Command('setup:install'),
            new Command('setup:upgrade'),
        ];

        $application = $this->getMockBuilder(Application::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->serviceLocatorMock
            ->expects($this->atLeastOnce())
            ->method('get')
            ->willReturnMap(
                [
                    [InitParamListener::BOOTSTRAP_PARAM, $initParams],
                    [
                        Application::class,
                        $application,
                    ],
                ]
            );

        $commandListMock = $this->createMock(CommandListInterface::class);
        $commandListMock->expects($this->once())
            ->method('getCommands')
            ->willReturn($commands);

        $objectManagerMock = $this->createMock(ObjectManagerInterface::class);
        $objectManagerMock->expects($this->once())
            ->method('create')
            ->with(CommandListInterface::class)
            ->willReturn($commandListMock);

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

        foreach ($commands as $command) {
            $this->assertSame($application, $command->getApplication());
        }
    }
}
