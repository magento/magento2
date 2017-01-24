<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Module\Di\App\Task;

use Magento\Setup\Module\Di\App\Task\Operation\InterceptionCache;

class InterceptionCacheTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Framework\Interception\Config\Config
     */
    private $configMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Setup\Module\Di\Code\Reader\Decorator\Interceptions
     */
    private $interceptionsListMock;

    public function setUp()
    {
        $this->configMock = $this->getMockBuilder(\Magento\Framework\Interception\Config\Config::class)
            ->setMethods([])
            ->disableOriginalConstructor()
            ->getMock();
        $this->interceptionsListMock = $this->getMockBuilder(
            \Magento\Setup\Module\Di\Code\Reader\Decorator\Interceptions::class
        )
            ->setMethods([])
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testDoOperationEmptyData()
    {
        $data = [];

        $operation = new InterceptionCache($this->configMock, $this->interceptionsListMock, $data);
        $this->configMock->expects($this->never())
            ->method('initialize');

        $this->assertNull($operation->doOperation());
    }

    public function testDoOperationInitializeWithDefinitions()
    {
        $definitions = [
            'Library\Class',
            'Application\Class',
            'VarGeneration\Class',
            'AppGeneration\Class'
        ];

        $data = [
            'lib',
            'app',
            'generation',
            'appgeneration'
        ];

        $this->interceptionsListMock->expects($this->any())
            ->method('getList')
            ->willReturnMap(
                [
                    ['lib', ['Library\Class']],
                    ['app', ['Application\Class']],
                    ['generation', ['VarGeneration\Class']],
                    ['appgeneration', ['AppGeneration\Class']]
                ]
            );

        $operation = new InterceptionCache($this->configMock, $this->interceptionsListMock, $data);
        $this->configMock->expects($this->once())
            ->method('initialize')
            ->with($definitions);

        $this->assertNull($operation->doOperation());
    }
}
