<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\CronJob;

use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Sales\Model\CronJob\AggregateSalesReportBestsellersData;
use Magento\Sales\Model\ResourceModel\Report\Bestsellers;
use Magento\Sales\Model\ResourceModel\Report\BestsellersFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Tests Magento\Sales\Model\CronJob\AggregateSalesReportBestsellersDataTest
 */
class AggregateSalesReportBestsellersDataTest extends TestCase
{
    /**
     * @var ResolverInterface|MockObject
     */
    protected $localeResolverMock;

    /**
     * @var TimezoneInterface|MockObject
     */
    protected $localeDateMock;

    /**
     * @var BestsellersFactory|MockObject
     */
    protected $bestsellersFactoryMock;

    /**
     * @var AggregateSalesReportBestsellersData
     */
    protected $observer;

    protected function setUp(): void
    {
        $this->localeResolverMock = $this->getMockBuilder(ResolverInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->bestsellersFactoryMock =
            $this->getMockBuilder(BestsellersFactory::class)
                ->disableOriginalConstructor()
                ->onlyMethods(['create'])
                ->getMock();
        $this->localeDateMock = $this->getMockBuilder(TimezoneInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->observer = new AggregateSalesReportBestsellersData(
            $this->localeResolverMock,
            $this->localeDateMock,
            $this->bestsellersFactoryMock
        );
    }

    public function testExecute()
    {
        $date = $this->setupAggregate();
        $bestsellersMock = $this->getMockBuilder(Bestsellers::class)
            ->disableOriginalConstructor()
            ->getMock();
        $bestsellersMock->expects($this->once())
            ->method('aggregate')
            ->with($date);
        $this->bestsellersFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($bestsellersMock);
        $this->observer->execute();
    }

    /**
     * Set up aggregate
     *
     * @return \DateTime
     */
    protected function setupAggregate()
    {
        $this->localeResolverMock->expects($this->once())
            ->method('emulate')
            ->with(0);
        $this->localeResolverMock->expects($this->once())
            ->method('revert');

        $date = (new \DateTime())->sub(new \DateInterval('PT25H'));
        $this->localeDateMock->expects($this->once())
            ->method('date')
            ->willReturn($date);

        return $date;
    }
}
