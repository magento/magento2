<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogSearch\Test\Unit\Model\Adapter;

use Magento\CatalogSearch\Model\Adapter\Options;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\ScopeInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class OptionsTest extends TestCase
{
    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $scopeConfig;

    /**
     * @var Options
     */
    private $options;

    protected function setUp(): void
    {
        $helper = new ObjectManager($this);

        $this->scopeConfig = $this->getMockBuilder(ScopeConfigInterface::class)
            ->setMethods(['getValue'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->options = $helper->getObject(
            Options::class,
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
