<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Backend\Test\Unit\Model\Dashboard;

use Magento\Backend\Model\Dashboard\Period;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

class PeriodTest extends TestCase
{
    /**
     * @var Period
     */
    private $model;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->model = $objectManager->getObject(
            Period::class,
            []
        );
    }

    /**
     * Test getDatePeriods() method
     */
    public function testGetDatePeriods()
    {
        $this->assertEquals(
            [
                Period::PERIOD_TODAY => (string)__('Today'),
                Period::PERIOD_24_HOURS => (string)__('Last 24 Hours'),
                Period::PERIOD_7_DAYS => (string)__('Last 7 Days'),
                Period::PERIOD_1_MONTH => (string)__('Current Month'),
                Period::PERIOD_1_YEAR => (string)__('YTD'),
                Period::PERIOD_2_YEARS => (string)__('2YTD')
            ],
            $this->model->getDatePeriods()
        );
    }
}
