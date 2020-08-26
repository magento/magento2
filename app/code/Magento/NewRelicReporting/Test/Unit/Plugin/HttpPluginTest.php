<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\NewRelicReporting\Test\Unit\Plugin;

use Exception;
use Magento\Framework\App\Bootstrap;
use Magento\Framework\App\Http;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\NewRelicReporting\Model\Config as NewRelicConfig;
use Magento\NewRelicReporting\Model\NewRelicWrapper;
use Magento\NewRelicReporting\Plugin\HttpPlugin;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test coverage for \Magento\NewRelicReporting\Plugin\HttpPlugin
 */
class HttpPluginTest extends TestCase
{
    /**
     * @var HttpPlugin
     */
    private $httpPlugin;

    /**
     * @var NewRelicConfig|MockObject
     */
    private $configMock;

    /**
     * @var NewRelicWrapper|MockObject
     */
    private $newRelicWrapperMock;

    /**
     * @var Http|MockObject
     */
    private $httpMock;

    /**
     * @var Bootstrap|MockObject
     */
    private $bootstrapMock;

    /**
     * @var Exception|MockObject
     */
    private $exceptionMock;

    /**
     * Set Up
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->configMock = $this->getMockBuilder(NewRelicConfig::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->newRelicWrapperMock = $this->createMock(NewRelicWrapper::class);
        $this->httpMock = $this->createMock(Http::class);
        $this->bootstrapMock = $this->createMock(Bootstrap::class);
        $this->exceptionMock = $this->createMock(Exception::class);

        $this->httpPlugin = $objectManager->getObject(
            HttpPlugin::class,
            [
                'config' => $this->configMock,
                'newRelicWrapper' => $this->newRelicWrapperMock,
            ]
        );
    }

    /**
     * Tests the thrown exception is reported to New Relic
     */
    public function testSuccessfullyReportingError(): void
    {
        $this->configMock->expects($this->once())->method('isNewRelicEnabled')->willReturn(true);
        $this->newRelicWrapperMock->expects($this->once())->method('reportError');

        $this->httpPlugin->beforeCatchException($this->httpMock, $this->bootstrapMock, $this->exceptionMock);
    }

    /**
     * Tests the thrown exception is not reported to New Relic
     */
    public function testNotReportingException(): void
    {
        $this->configMock->expects($this->once())->method('isNewRelicEnabled')->willReturn(false);
        $this->newRelicWrapperMock->expects($this->never())->method('reportError');

        $this->httpPlugin->beforeCatchException($this->httpMock, $this->bootstrapMock, $this->exceptionMock);
    }
}
