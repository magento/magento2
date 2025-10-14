<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Model\Description\Mixin;

use Magento\Setup\Model\Description\Mixin\ParagraphMixin;
use PHPUnit\Framework\TestCase;

class ParagraphMixinTest extends TestCase
{
    /**
     * @var ParagraphMixin
     */
    private $mixin;

    protected function setUp(): void
    {
        $this->mixin = new ParagraphMixin();
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
            ['', '<p></p>'],
            [
                'Lorem ipsum dolor sit amet.' . PHP_EOL
                . 'Consectetur adipiscing elit.' . PHP_EOL
                . 'Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',

                '<p>Lorem ipsum dolor sit amet.</p>' . PHP_EOL
                . '<p>Consectetur adipiscing elit.</p>' . PHP_EOL
                . '<p>Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>'
            ]
        ];
    }
}
