<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Test\Unit\Cron;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class FetchReportsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $objectManager;

    /**
     * @var \Magento\Paypal\Cron\FetchReports
     */
    private $fetchReports;

    /**
     * @var \Magento\Paypal\Model\Report\SettlementFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $settlementFactoryMock;

    /**
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $objectManagerMock;

    /**
     * @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $logger;

    protected function setUp()
    {
        $this->objectManagerMock = $this->getMockBuilder(\Magento\Framework\ObjectManagerInterface::class)
            ->getMock();
        $this->settlementFactoryMock = $this->getMockBuilder(\Magento\Paypal\Model\Report\SettlementFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->logger = $this->getMockForAbstractClass(\Psr\Log\LoggerInterface::class);

        $this->objectManager = new ObjectManager($this);
        $this->fetchReports = $this->objectManager->getObject(
            \Magento\Paypal\Cron\FetchReports::class,
            [
                'settlementFactory' => $this->settlementFactoryMock
            ]
        );
    }

    /**
     * @expectedException \Exception
     */
    public function testExecuteThrowsException()
    {
        $sftpCredentials = [
            'hostname' => ['test_hostname'],
            'username' => ['test_username'],
            'password' => ['test_password'],
            'path' => ['test_path']
        ];
        $settlementMock = $this->getMockBuilder(\Magento\Paypal\Model\Report\Settlement::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->settlementFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($settlementMock);

        $settlementMock->expects($this->once())->method('getSftpCredentials')->with(true)->willReturn($sftpCredentials);
        $settlementMock->expects($this->any())->method('fetchAndSave')->willThrowException(new \Exception);
        $this->logger->expects($this->never())->method('critical');

        $this->fetchReports->execute();
    }
}
