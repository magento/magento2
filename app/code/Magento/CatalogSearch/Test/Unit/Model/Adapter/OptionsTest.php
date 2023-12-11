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

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $helper = new ObjectManager($this);

        $this->scopeConfig = $this->getMockBuilder(ScopeConfigInterface::class)
            ->onlyMethods(['getValue'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->options = $helper->getObject(
            Options::class,
            [
                'scopeConfig' => $this->scopeConfig
            ]
        );
    }

    /**
     * @return void
     */
    public function testGet(): void
    {
        $expectedResult = [
            'interval_division_limit' => 15,
            'range_step' => 3.3,
            'min_range_power' => 10,
            'max_intervals_number' => 33
        ];

        $this->scopeConfig
            ->method('getValue')
            ->willReturnOnConsecutiveCalls(
                $expectedResult['interval_division_limit'],
                $expectedResult['range_step'],
                $expectedResult['max_intervals_number']
            );

        $this->options->get();
    }
}
