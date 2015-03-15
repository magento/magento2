<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Translation\Test\Unit\Model\Js\Config\Source;

use Magento\Translation\Model\Js\Config;
use Magento\Translation\Model\Js\Config\Source\Strategy;

/**
 * Class StrategyTest
 */
class StrategyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Strategy
     */
    protected $model;

    /**
     * Set up
     * @return void
     */
    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $objectManager->getObject('Magento\Translation\Model\Js\Config\Source\Strategy');
    }

    /**
     * Test for toOptionArray method
     * @return void
     */
    public function testToOptionArray()
    {
        $expected = [
            ['label' => __('None (Translation is disabled)'), 'value' => Config::NO_TRANSLATION],
            ['label' => 'Dictionary (Translation on frontend side)', 'value' => Config::DICTIONARY_STRATEGY],
            ['label' => 'Embedded (Translation on backend side)', 'value' => Config::EMBEDDED_STRATEGY]
        ];
        $this->assertEquals($expected, $this->model->toOptionArray());
    }
}
