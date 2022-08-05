<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Stdlib\DateTime\Filter;

use Magento\TestFramework\ObjectManager;

class DateTest extends \PHPUnit\Framework\TestCase
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
     * @var \Magento\Framework\Stdlib\DateTime\Filter\Date
     */
    private $dateFilter;

    protected function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        $this->localeResolver = $this->objectManager->get(\Magento\Framework\Locale\ResolverInterface::class);

        $this->localeDate = $this->objectManager->get(\Magento\Framework\Stdlib\DateTime\TimezoneInterface::class, [
            'localeResolver' => $this->localeResolver
        ]);

        $this->dateFilter = $this->objectManager->get(\Magento\Framework\Stdlib\DateTime\Filter\Date::class, [
            'localeDate' => $this->localeDate
        ]);
    }

    /**
     * @param string $inputData
     * @param string $expectedDate
     *
     * @dataProvider filterDataProvider
     */
    public function testFilter($inputData, $expectedDate)
    {
        $this->markTestSkipped(
            'Input data not realistic with actual request payload from admin UI. See MAGETWO-59810'
        );
        $this->assertEquals($expectedDate, $this->dateFilter->filter($inputData));
    }

    /**
     * @return array
     */
    public function filterDataProvider()
    {
        return [
            ['2000-01-01', '2000-01-01'],
            ['2014-03-30T02:30:00', '2014-03-30'],
            ['12/31/2000', '2000-12-31']
        ];
    }

    /**
     * @param string $locale
     * @param string $inputData
     * @param string $expectedDate
     *
     * @dataProvider localeDateFilterProvider
     * @return void
     */
    public function testLocaleDateFilter($locale, $inputData, $expectedDate)
    {
        $this->localeResolver->setLocale($locale);
        $this->assertEquals($expectedDate, $this->dateFilter->filter($inputData));
    }

    /**
     * @return array
     */
    public function localeDateFilterProvider()
    {
        return [
            ['en_US', '01/02/2010', '2010-01-02'],
            ['fr_FR', '01/02/2010', '2010-02-01'],
            ['de_DE', '01/02/2010', '2010-02-01'],
        ];
    }
}
