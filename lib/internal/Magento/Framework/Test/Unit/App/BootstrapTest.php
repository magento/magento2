<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Test\Unit\App;

use Magento\Framework\App\Bootstrap;
use Magento\Framework\App\ObjectManagerFactory;
use Magento\Framework\AppInterface;
use Magento\Framework\HTTP\PhpEnvironment\Response;
use Magento\Framework\ObjectManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

#[
    CoversClass(Bootstrap::class),
]
class BootstrapTest extends TestCase
{
    /**
     * @var Bootstrap
     */
    private $bootstrap;

    /**
     * @var Response|MockObject
     */
    private $responseMock;

    protected function setUp(): void
    {
        $loggerMock = $this->createMock(LoggerInterface::class);
        $this->responseMock = $this->createMock(Response::class);
        $objectManagerMock = $this->createMock(ObjectManagerInterface::class);
        $objectManagerMock->method('get')->willReturnMap(
            [
                [LoggerInterface::class, $loggerMock],
                [Response::class, $this->responseMock],
            ]
        );
        $objectManagerFactoryMock = $this->createMock(ObjectManagerFactory::class);
        $objectManagerFactoryMock->expects(self::once())->method('create')->willReturn($objectManagerMock);

        $initParams = [
            'MAGE_REQUIRE_MAINTENANCE' => null,
            'MAGE_REQUIRE_IS_INSTALLED' => null,
            'MAGE_MODE' => 'default',
        ];
        $this->bootstrap = new Bootstrap(
            $objectManagerFactoryMock,
            '',
            $initParams,
        );
    }

    public function testRunWithException(): void
    {
        $applicationMock = $this->createMock(AppInterface::class);
        $applicationMock->expects(self::once())->method('launch')->willThrowException(new \Exception());
        $applicationMock->expects(self::once())->method('catchException')->willReturn(false);

        $this->responseMock->expects(self::once())->method('clearHeaders')->willReturnSelf();
        $this->responseMock->expects(self::once())->method('setHttpResponseCode')->with(500)->willReturnSelf();

        // Throw exception from sendResponse to stop the method execution and prevent the process from termination
        $e = new \Exception();
        $this->responseMock->expects(self::once())->method('sendResponse')->willThrowException($e);
        self::expectExceptionObject($e);

        $this->bootstrap->run($applicationMock);
    }
}
