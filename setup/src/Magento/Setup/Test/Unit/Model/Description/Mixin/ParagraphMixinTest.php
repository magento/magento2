<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Model\Description\Mixin;

use Magento\Setup\Model\Description\Mixin\ParagraphMixin;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

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
     */
    #[DataProvider('getTestData')]
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
