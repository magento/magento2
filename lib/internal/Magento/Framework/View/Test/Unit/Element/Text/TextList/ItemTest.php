<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

/**
 * Test for view BlockPool model
 */
namespace Magento\Framework\View\Test\Unit\Element\Text\TextList;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Element\Text\TextList\Item;
use PHPUnit\Framework\TestCase;

class ItemTest extends TestCase
{
    /**
     * @var Item
     */
    protected $item;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->item = $objectManager->getObject(Item::class);
    }

    public function testSetLink()
    {
        $liParams = ['class' => 'some-css-class'];
        $innerText = 'text';

        $this->assertNull($this->item->getLiParams());
        $this->assertNull($this->item->getInnerText());

        $this->item->setLink($liParams, $innerText);

        $this->assertEquals($liParams, $this->item->getLiParams());
        $this->assertEquals($innerText, $this->item->getInnerText());
    }

    /**
     * @dataProvider toHtmlDataProvider
     *
     * @param array $liParams
     * @param string $innerText
     */
    public function testToHtml($liParams, $innerText, $expectedHtml)
    {
        $this->item->setLink($liParams, $innerText);

        $this->assertEquals($expectedHtml, $this->item->toHtml());
    }

    /**
     * @return array
     */
    public function toHtmlDataProvider()
    {
        return [
            [
                'liParams' => ['class' => 'some-css-class'],
                'innerText' => 'text',
                'expectedHtml' => '<li class="some-css-class">text</li>' . "\r\n"
            ],
            [
                'liParams' => 'class="some-css-class"',
                'innerText' => 'text',
                'expectedHtml' => '<li class="some-css-class">text</li>' . "\r\n"
            ]
        ];
    }
}
