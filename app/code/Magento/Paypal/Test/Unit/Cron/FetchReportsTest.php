<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Paypal\Test\Unit\Cron;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Paypal\Cron\FetchReports;
use Magento\Paypal\Model\Report\Settlement;
use Magento\Paypal\Model\Report\SettlementFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class FetchReportsTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var FetchReports
     */
    private $fetchReports;

    /**
     * @var SettlementFactory|MockObject
     */
    private $settlementFactoryMock;

    /**
     * @var ObjectManagerInterface|MockObject
     */
    private $objectManagerMock;

    /**
     * @var LoggerInterface|MockObject
     */
    private $logger;

    protected function setUp(): void
    {
        $this->objectManagerMock = $this->getMockBuilder(ObjectManagerInterface::class)
            ->getMock();
        $this->settlementFactoryMock = $this->getMockBuilder(SettlementFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->logger = $this->getMockForAbstractClass(LoggerInterface::class);

        $this->objectManager = new ObjectManager($this);
        $this->fetchReports = $this->objectManager->getObject(
            FetchReports::class,
            [
                'settlementFactory' => $this->settlementFactoryMock
            ]
        );
    }

    public function testExecuteThrowsException()
    {
        $this->expectException('Exception');
        $sftpCredentials = [
            'hostname' => ['test_hostname'],
            'username' => ['test_username'],
            'password' => ['test_password'],
            'path' => ['test_path']
        ];
        $settlementMock = $this->getMockBuilder(Settlement::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->settlementFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($settlementMock);

        $settlementMock->expects($this->once())->method('getSftpCredentials')->with(true)->willReturn($sftpCredentials);
        $settlementMock->expects($this->any())->method('fetchAndSave')->willThrowException(new \Exception());
        $this->logger->expects($this->never())->method('critical');

        $this->fetchReports->execute();
    }
}
