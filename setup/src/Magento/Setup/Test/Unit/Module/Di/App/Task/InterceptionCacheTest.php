<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Module\Di\App\Task;

use Magento\Framework\Interception\Config\Config;
use Magento\Setup\Module\Di\App\Task\Operation\InterceptionCache;
use Magento\Setup\Module\Di\Code\Reader\Decorator\Interceptions;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class InterceptionCacheTest extends TestCase
{
    /**
     * @var MockObject|Config
     */
    private $configMock;

    /**
     * @var MockObject|Interceptions
     */
    private $interceptionsListMock;

    protected function setUp(): void
    {
        $this->configMock = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->interceptionsListMock = $this->getMockBuilder(
            Interceptions::class
        )
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
