<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Stdlib\DateTime\Filter;

class DataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime\Filter\Date
     */
    private $dataFilter;

    protected function setUp()
    {
        $this->dataFilter = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Framework\Stdlib\DateTime\Filter\Date::class
        );
    }

    /**
     * @param string $inputData
     * @param string $expectedDate
     *
     * @dataProvider filterDataProvider
     */
    public function testFilter($inputData, $expectedDate)
    {
        $this->assertEquals($expectedDate, $this->dataFilter->filter($inputData));
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
}
