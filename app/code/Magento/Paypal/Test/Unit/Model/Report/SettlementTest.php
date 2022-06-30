<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Paypal\Test\Unit\Model\Report;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Paypal\Model\Report\Settlement;
use Magento\Framework\Filesystem\Io\Sftp;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for paypal settlement
 */
class SettlementTest extends TestCase
{
    /**
     * @var Settlement
     */
    private $settlement;

    /**
     * @var WriteInterface|MockObject
     */
    private $tmpDirectory;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->tmpDirectory = $this->getMockBuilder(WriteInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->settlement = $objectManagerHelper->getObject(
            Settlement::class,
            [
                '_tmpDirectory' => $this->tmpDirectory
            ]
        );
    }

    /**
     * Test for filter report list
     *
     * @return void
     */
    public function testFilterReportList(): void
    {
        $this->tmpDirectory->method('getAbsolutePath')
            ->willReturn('');
        /** @var Sftp|MockObject $connection */
        $connection = $this->getMockBuilder(Sftp::class)
            ->onlyMethods(['rawls', 'read'])
            ->disableOriginalConstructor()
            ->getMock();
        $connection->method('rawls')
            ->willReturn(
                [
                    'STL-20201221.01.009.CSV' => 'Single account',
                    'STL-20201221.H.01.01.009.CSV' => 'Multiple account',
                ]
            );
        $connection->expects($this->exactly(2))->method('read')->willReturn(false);
        $this->settlement->fetchAndSave($connection);
    }
}
