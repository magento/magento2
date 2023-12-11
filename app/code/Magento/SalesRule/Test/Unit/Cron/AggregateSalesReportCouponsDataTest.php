<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Test\Unit\Cron;

use Magento\Cron\Model\Schedule;
use Magento\Framework\Locale\Resolver;
use Magento\Framework\Stdlib\DateTime\Timezone;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\SalesRule\Cron\AggregateSalesReportCouponsData;
use Magento\SalesRule\Model\ResourceModel\Report\Rule;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AggregateSalesReportCouponsDataTest extends TestCase
{
    /**
     * @var AggregateSalesReportCouponsData|MockObject
     */
    protected $model;

    /**
     * @var Resolver|MockObject
     */
    protected $localeResolver;

    /**
     * @var Timezone|MockObject
     */
    protected $localeDate;

    /**
     * @var Rule|MockObject
     */
    protected $reportRule;

    protected function setUp(): void
    {
        $helper = new ObjectManager($this);
        $this->initMocks();

        $this->model = $helper->getObject(
            AggregateSalesReportCouponsData::class,
            [
                'reportRule' => $this->reportRule,
                'localeResolver' => $this->localeResolver,
                'localeDate' => $this->localeDate,
            ]
        );
    }

    protected function initMocks()
    {
        $this->localeResolver = $this->createMock(Resolver::class);
        $this->localeDate = $this->createPartialMock(Timezone::class, ['date']);
        $this->reportRule = $this->createMock(Rule::class);
    }

    public function testExecute()
    {
        $data = new \DateTime();
        $this->localeResolver->expects($this->once())
            ->method('emulate')
            ->with(0);
        $this->localeDate->expects($this->once())
            ->method('date')
            ->willReturn($data);
        $this->reportRule->expects($this->once())
            ->method('aggregate')
            ->with($data);
        $this->localeResolver->expects($this->once())
            ->method('revert');

        $scheduleMock = $this->createMock(Schedule::class);

        $this->assertEquals($this->model, $this->model->execute($scheduleMock));
    }
}
