<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Translation\Test\Unit\Model\Js\Config\Source;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Translation\Model\Js\Config;
use Magento\Translation\Model\Js\Config\Source\Strategy;
use PHPUnit\Framework\TestCase;

class StrategyTest extends TestCase
{
    /**
     * @var Strategy
     */
    protected $model;

    /**
     * Set up
     * @return void
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->model = $objectManager->getObject(Strategy::class);
    }

    /**
     * Test for toOptionArray method
     * @return void
     */
    public function testToOptionArray()
    {
        $expected = [
            ['label' => 'Dictionary (Translation on Storefront side)', 'value' => Config::DICTIONARY_STRATEGY],
            ['label' => 'Embedded (Translation on Admin side)', 'value' => Config::EMBEDDED_STRATEGY]
        ];
        $this->assertEquals($expected, $this->model->toOptionArray());
    }
}
