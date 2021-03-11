<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Directory\Test\Unit\Model;

class TopDestinationCountriesTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfigMock;

    /**
     * @var \Magento\Directory\Model\TopDestinationCountries
     */
    protected $model;

    protected function setUp(): void
    {
        $this->scopeConfigMock = $this->getMockBuilder(\Magento\Framework\App\Config\ScopeConfigInterface::class)
            ->getMock();
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $arguments = [
            'scopeConfig' => $this->scopeConfigMock
        ];
        $this->model = $objectManager
            ->getObject(\Magento\Directory\Model\TopDestinationCountries::class, $arguments);
    }

    /**
     * @dataProvider toTestGetTopDestinationsDataProvider
     */
    public function testGetTopDestinations($options, $expectedResults)
    {
        $this->scopeConfigMock->expects($this->once())->method('getValue')->willReturn($options);
        $this->assertEquals($expectedResults, $this->model->getTopDestinations());
    }

    /**
     * @return array
     */
    public function toTestGetTopDestinationsDataProvider()
    {
        return [
            ['UA,AF', ['UA', 'AF']],
            ['', []]
        ];
    }
}
