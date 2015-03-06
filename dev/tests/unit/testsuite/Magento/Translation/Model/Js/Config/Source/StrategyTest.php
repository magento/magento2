<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Translation\Model\Js\Config\Source;

use Magento\Translation\Model\Js\Config;

class StrategyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Strategy
     */
    protected $model;

    protected function setUp()
    {
        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->model = $objectManager->getObject('Magento\Translation\Model\Js\Config\Source\Strategy');
    }

    public function testToOptionArray()
    {
        $expected = [
            ['label' => 'Dictionary (Translation on frontend side)', 'value' => Config::DICTIONARY_STRATEGY],
            ['label' => 'Embedded (Translation on backend side)', 'value' => Config::EMBEDDED_STRATEGY]
        ];
        $this->assertEquals($expected, $this->model->toOptionArray());
    }
}
