<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Product\Filter;

use Magento\Catalog\Model\Product\Filter\DateTime;
use Magento\Framework\Locale\Resolver;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Stdlib\DateTime\Intl\DateFormatterFactory;
use Magento\Framework\Stdlib\DateTime\Timezone;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

class DateTimeTest extends TestCase
{
    /**
     * @var string
     */
    private $locale;
    /**
     * @var DateTime
     */
    private $model;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $objectManager = new ObjectManager($this);
        $this->locale = Resolver::DEFAULT_LOCALE;
        $localeResolver = $this->getMockForAbstractClass(ResolverInterface::class);
        $localeResolver->expects($this->any())
            ->method('getLocale')
            ->willReturnCallback(
                function () {
                    return $this->locale;
                }
            );
        $timezone = $objectManager->getObject(
            Timezone::class,
            ['localeResolver' => $localeResolver, 'dateFormatterFactory' => new DateFormatterFactory()]
        );
        $stdlibDateTimeFilter = $objectManager->getObject(
            \Magento\Framework\Stdlib\DateTime\Filter\DateTime::class,
            ['localeDate' => $timezone]
        );
        $this->model = $objectManager->getObject(
            DateTime::class,
            [
                'stdlibDateTimeFilter' => $stdlibDateTimeFilter
            ]
        );
    }

    /**
     * Test filter with different dates formats and locales
     *
     * @dataProvider provideFilter
     */
    public function testFilter(string $date, string $expectedDate, string $locale = Resolver::DEFAULT_LOCALE)
    {
        $this->locale = $locale;
        $this->assertEquals($expectedDate, $this->model->filter($date));
    }

    /**
     * Provide date formats and locales
     *
     * @return array
     */
    public function provideFilter(): array
    {
        return [
            ['1999-12-31', '1999-12-31 00:00:00', 'en_US'],
            ['12-31-1999', '1999-12-31 00:00:00', 'en_US'],
            ['12/31/1999', '1999-12-31 00:00:00', 'en_US'],
            ['December 31, 1999', '1999-12-31 00:00:00', 'en_US'],
            ['1999-12-31', '1999-12-31 00:00:00', 'fr_FR'],
            ['31-12-1999', '1999-12-31 00:00:00', 'fr_FR'],
            ['31/12/1999', '1999-12-31 00:00:00', 'fr_FR'],
            ['31 Décembre 1999', '1999-12-31 00:00:00', 'fr_FR'],
        ];
    }
}
