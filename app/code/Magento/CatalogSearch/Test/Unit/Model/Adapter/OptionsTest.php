<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Test\Unit\Model\Adapter;

use Magento\CatalogSearch\Model\Adapter\Options;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class OptionsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $scopeConfig;

    /**
     * @var Options
     */
    private $options;

    protected function setUp()
    {
        $helper = new ObjectManager($this);

        $this->scopeConfig = $this->getMockBuilder(\Magento\Framework\App\Config\ScopeConfigInterface::class)
            ->setMethods(['getValue'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->options = $helper->getObject(
            \Magento\CatalogSearch\Model\Adapter\Options::class,
            [
                'scopeConfig' => $this->scopeConfig
            ]
        );
    }

    public function testGet()
    {
        $expectedResult = [
            'interval_division_limit' => 15,
            'range_step' => 3.3,
            'min_range_power' => 10,
            'max_intervals_number' => 33
        ];

        $this->scopeConfig->expects($this->at(0))
            ->method('getValue')
            ->withConsecutive([Options::XML_PATH_INTERVAL_DIVISION_LIMIT, ScopeInterface::SCOPE_STORE])
            ->willReturn($expectedResult['interval_division_limit']);
        $this->scopeConfig->expects($this->at(1))
            ->method('getValue')
            ->withConsecutive([Options::XML_PATH_RANGE_STEP, ScopeInterface::SCOPE_STORE])
            ->willReturn($expectedResult['range_step']);
        $this->scopeConfig->expects($this->at(2))
            ->method('getValue')
            ->withConsecutive([Options::XML_PATH_RANGE_MAX_INTERVALS, ScopeInterface::SCOPE_STORE])
            ->willReturn($expectedResult['max_intervals_number']);

        $this->options->get();
    }
}
