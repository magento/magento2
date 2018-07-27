<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test for view BlockPool model
 */
namespace Magento\Framework\View\Test\Unit\Element\Text\TextList;

use \Magento\Framework\View\Element\Text\TextList\Item;

class ItemTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Item
     */
    protected $item;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->item = $objectManager->getObject('Magento\Framework\View\Element\Text\TextList\Item');
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
     * @param string $attrName
     * @param string $attrValue
     * @param string $innerText
     */
    public function testToHtml($liParams, $attrName, $attrValue, $innerText)
    {
        $this->item->setLink($liParams, $innerText);
        $this->assertTag([
            'tag' => 'li',
            'attributes' => [$attrName => $attrValue],
            'content' => $innerText,
        ], $this->item->toHtml());
    }

    /**
     * @return array
     */
    public function toHtmlDataProvider()
    {
        return [
            [
                'liParams' => ['class' => 'some-css-class'],
                'attrName' => 'class',
                'attrValue' => 'some-css-class',
                'innerText' => 'text',
            ],
            [
                'liParams' => 'class="some-css-class"',
                'attrName' => 'class',
                'attrValue' => 'some-css-class',
                'innerText' => 'text',
            ]
        ];
    }
}
