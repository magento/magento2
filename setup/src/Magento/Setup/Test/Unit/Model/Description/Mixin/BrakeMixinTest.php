<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Model\Description\Mixin;

use Magento\Setup\Model\Description\Mixin\BrakeMixin;
use PHPUnit\Framework\TestCase;

class BrakeMixinTest extends TestCase
{
    /**
     * @var BrakeMixin
     */
    private $mixin;

    protected function setUp(): void
    {
        $this->mixin = new BrakeMixin();
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
    public static function getTestData()
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
