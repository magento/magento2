<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Phrase\Test\Unit\Renderer;

use Magento\Framework\Phrase\Renderer\Placeholder;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

class PlaceholderTest extends TestCase
{
    /** @var Placeholder */
    protected $_renderer;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->_renderer = $objectManager->getObject(Placeholder::class);
    }

    /**
     * @param string $text The text with placeholders
     * @param array $arguments The arguments supplying values for the placeholders
     * @param string $result The result of Phrase rendering
     *
     * @dataProvider renderPlaceholderDataProvider
     */
    public function testRenderPlaceholder($text, array $arguments, $result)
    {
        $this->assertEquals($result, $this->_renderer->render([$text], $arguments));
    }

    /**
     * @return array
     */
    public static function renderPlaceholderDataProvider()
    {
        return [
            ['text %1 %2', ['one', 'two'], 'text one two'],
            ['text %one %two', ['one' => 'one', 'two' => 'two'], 'text one two'],
            ['%one text %two %1', ['one' => 'one', 'two' => 'two', 'three'], 'one text two three'],
            [
                'text %1 %two %2 %3 %five %4 %5',
                ['one', 'two' => 'two', 'three', 'four', 'five' => 'five', 'six', 'seven'],
                'text one two three four five six seven'
            ],
            [
                '%one text %two text %three %1 %2',
                ['two' => 'two', 'one' => 'one', 'three' => 'three', 'four', 'five'],
                'one text two text three four five'
            ],
            [
                '%three text %two text %1',
                ['two' => 'two', 'three' => 'three', 'one'],
                'three text two text one'
            ],
            ['text %1 text %2 text', [], 'text %1 text %2 text'],
            ['%1 text %2', ['one'], 'one text %2'],
            [
                '%1 %2 %3 %4 %5 %6 %7 %8 %9 %10 %11',
                ['one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine', 'ten', 'eleven'],
                'one two three four five six seven eight nine ten eleven',
            ],
            [
                'A %table has four legs',
                ['tab' => 'Tab-Leiste', 'able' => '', 'table' => 'Tabelle'], 'A Tabelle has four legs'
            ],
        ];
    }
}
