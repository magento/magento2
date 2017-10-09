<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Stdlib\DateTime\Filter;

use Magento\TestFramework\ObjectManager;

class DateTimeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    private $localeResolver;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    private $localeDate;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\Filter\DateTime
     */
    private $dateTimeFilter;

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        $this->localeResolver = $this->objectManager->get(\Magento\Framework\Locale\ResolverInterface::class);

        $this->localeDate = $this->objectManager->get(\Magento\Framework\Stdlib\DateTime\TimezoneInterface::class, [
            'localeResolver' => $this->localeResolver
        ]);

        $this->dateTimeFilter = $this->objectManager->get(\Magento\Framework\Stdlib\DateTime\Filter\DateTime::class, [
            'localeDate' => $this->localeDate
        ]);
    }

    /**
     * @param string $locale
     * @param string $inputData
     * @param string $expectedDate
     *
     * @dataProvider localeDatetimeFilterProvider
     * @return void
     */
    public function testLocaleDatetimeFilter($locale, $inputData, $expectedDate)
    {
        $this->localeResolver->setLocale($locale);
        $this->assertEquals($expectedDate, $this->dateTimeFilter->filter($inputData));
    }

    /**
     * @return array
     */
    public function localeDatetimeFilterProvider()
    {
        return [
            ['en_US', '01/02/2010 3:30pm', '2010-01-02 15:30:00'],
            ['en_US', '01/02/2010 1:00am', '2010-01-02 01:00:00'],
            ['en_US', '01/02/2010 01:00am', '2010-01-02 01:00:00'],
            ['fr_FR', '01/02/2010 15:30', '2010-02-01 15:30:00'],
            ['fr_FR', '01/02/2010 1:00', '2010-02-01 01:00:00'],
            ['fr_FR', '01/02/2010 01:00', '2010-02-01 01:00:00'],
            ['en_US', '11/28/2010', '2010-11-28 00:00:00'],
            ['en_US', '11/28/2010 1:00am', '2010-11-28 01:00:00'],
            ['en_US', '11/28/2010 01:00am', '2010-11-28 01:00:00'],
            ['es_ES', '28/11/2010', '2010-11-28 00:00:00'],
            ['es_ES', '28/11/2010 23:12:00', '2010-11-28 23:12:00'],
            ['es_ES', '28/11/2010 23:12', '2010-11-28 23:12:00'],
            ['de_DE', '01/02/2010 15:30', '2010-02-01 15:30:00'],
            ['en_US', '2017-09-01T15:30:00.000Z', '2017-09-01 15:30:00'],
            ['fr_FR', '2017-09-01T15:30:00.000Z', '2017-09-01 15:30:00']
        ];
    }
}
