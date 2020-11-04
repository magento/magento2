<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Model\Description\Mixin;

use Magento\Setup\Model\Description\Mixin\HeaderMixin;
use PHPUnit\Framework\TestCase;

class HeaderMixinTest extends TestCase
{
    /**
     * @var HeaderMixin
     */
    private $mixin;

    protected function setUp(): void
    {
        $this->mixin = new HeaderMixin();
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

                '<h1>Lorem ipsum</h1>' . PHP_EOL
                . 'Lorem ipsum dolor sit amet.' . PHP_EOL
                . '<h1>Consectetur</h1>' . PHP_EOL
                . 'Consectetur adipiscing elit.' . PHP_EOL
                . '<h1>Sed do eiusmod</h1>' . PHP_EOL
                . 'Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.'
            ]
        ];
    }
}
