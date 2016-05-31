<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Test\Unit\Cron;

class AggregateSalesReportCouponsDataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\SalesRule\Cron\AggregateSalesReportCouponsData|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $model;

    /**
     * @var \Magento\Framework\Locale\Resolver|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $localeResolver;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\Timezone|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $localeDate;

    /**
     * @var \Magento\SalesRule\Model\ResourceModel\Report\Rule|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $reportRule;

    protected function setUp()
    {
        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->initMocks();

        $this->model = $helper->getObject(
            'Magento\SalesRule\Cron\AggregateSalesReportCouponsData',
            [
                'reportRule' => $this->reportRule,
                'localeResolver' => $this->localeResolver,
                'localeDate' => $this->localeDate,
            ]
        );
    }

    protected function initMocks()
    {
        $this->localeResolver = $this->getMock('Magento\Framework\Locale\Resolver', [], [], '', false);
        $this->localeDate = $this->getMock('Magento\Framework\Stdlib\DateTime\Timezone', ['date'], [], '', false);
        $this->reportRule = $this->getMock('Magento\SalesRule\Model\ResourceModel\Report\Rule', [], [], '', false);
    }

    public function testExecute()
    {
        $data = new \DateTime();
        $this->localeResolver->expects($this->once())
            ->method('emulate')
            ->with(0);
        $this->localeDate->expects($this->once())
            ->method('date')
            ->will($this->returnValue($data));
        $this->reportRule->expects($this->once())
            ->method('aggregate')
            ->with($data);
        $this->localeResolver->expects($this->once())
            ->method('revert');

        $scheduleMock = $this->getMock('Magento\Cron\Model\Schedule', [], [], '', false);

        $this->assertEquals($this->model, $this->model->execute($scheduleMock));
    }
}
