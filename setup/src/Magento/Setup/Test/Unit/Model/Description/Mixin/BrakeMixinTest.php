<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Model\Description\Mixin;

class BrakeMixinTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Setup\Model\Description\Mixin\BrakeMixin
     */
    private $mixin;

    public function setUp()
    {
        $this->mixin = new \Magento\Setup\Model\Description\Mixin\BrakeMixin();
    }

    /**
     * @dataProvider getTestData
     */
    public function testApply($subject, $expectedResult)
    {
        $this->assertEquals($expectedResult, $this->mixin->apply($subject));
    }

    /**
     * @return array
     */
    public function getTestData()
    {
        return [
            ['', ''],
            [
                'Lorem ipsum dolor sit amet.' . PHP_EOL
                . 'Consectetur adipiscing elit.' . PHP_EOL
                . 'Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',

                'Lorem ipsum dolor sit amet.' . PHP_EOL
                . '</br>' . PHP_EOL
                . 'Consectetur adipiscing elit.' . PHP_EOL
                . '</br>' . PHP_EOL
                . 'Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.'
            ]
        ];
    }
}
