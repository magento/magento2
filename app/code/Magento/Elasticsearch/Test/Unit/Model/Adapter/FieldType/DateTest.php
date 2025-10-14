<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Test\Unit\Model\Adapter\FieldType;

use Magento\Elasticsearch\Model\Adapter\FieldType\Date as DateField;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DateTest extends TestCase
{
    /**
     * @var DateField
     */
    private $model;

    /**
     * @var DateTime|MockObject
     */
    private $dateTime;

    /**
     * @var TimezoneInterface|MockObject
     */
    private $localeDate;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $scopeConfig;

    /**
     * Set up test environment
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->dateTime = $this->getMockBuilder(DateTime::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['isEmptyDate'])
            ->getMock();

        $this->localeDate = $this->getMockBuilder(TimezoneInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->scopeConfig = $this->getMockBuilder(ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $objectManager = new ObjectManagerHelper($this);
        $this->model = $objectManager->getObject(
            \Magento\Elasticsearch\Model\Adapter\FieldType\Date::class,
            [
                'dateTime' => $this->dateTime,
                'localeDate' => $this->localeDate,
                'scopeConfig' => $this->scopeConfig
            ]
        );
    }

    /**
     * Test formatDate() method
     */
    public function testFormatDateEmpty()
    {
        $this->dateTime->expects($this->once())
            ->method('isEmptyDate')
            ->willReturn(true);
        $this->assertNull($this->model->formatDate(1, null));
    }

    /**
     * Test formatDate() method
     */
    public function testFormatDate()
    {
        $this->dateTime->expects($this->once())
            ->method('isEmptyDate')
            ->willReturn(false);
        $this->assertEquals('1997-12-31T00:00:00+00:00', $this->model->formatDate(1, '1997-12-31'));
    }
}
