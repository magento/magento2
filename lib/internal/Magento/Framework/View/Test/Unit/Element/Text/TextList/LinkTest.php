<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test for view BlockPool model
 */
namespace Magento\Framework\View\Test\Unit\Element\Text\TextList;

use \Magento\Framework\View\Element\Text\TextList\Link;

class LinkTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Link
     */
    protected $link;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->link = $objectManager->getObject(\Magento\Framework\View\Element\Text\TextList\Link::class);
    }

    public function testSetLink()
    {
        $liParams = ['class' => 'some-css-class'];
        $aParams = ['href' => 'url'];
        $innerText = 'text';
        $afterText = 'afterText';

        $this->assertNull($this->link->getLiParams());
        $this->assertNull($this->link->getAParams());
        $this->assertNull($this->link->getInnerText());
        $this->assertNull($this->link->getAfterText());

        $this->link->setLink($liParams, $aParams, $innerText, $afterText);

        $this->assertEquals($liParams, $this->link->getLiParams());
        $this->assertEquals($aParams, $this->link->getAParams());
        $this->assertEquals($innerText, $this->link->getInnerText());
        $this->assertEquals($afterText, $this->link->getAfterText());
    }

    /**
     * @dataProvider toHtmlDataProvider
     */
    public function testToHtml($liParams, $liAttr, $aParams, $aAttr, $innerText, $afterText)
    {
        $this->markTestSkipped('Test needs to be refactored.');
        $this->link->setLink($liParams, $aParams, $innerText, $afterText);
        $this->assertTag([
            'tag' => 'li',
            'attributes' => [$liAttr['name'] => $liAttr['value']],
            'child' => [
                'tag' => 'a',
                'attributes' => [$aAttr['name'] => $aAttr['value']],
                'content' => $innerText,
            ],
            'content' => $afterText,
        ], $this->link->toHtml());
    }

    public function toHtmlDataProvider()
    {
        return [
            [
                'liParams' => ['class' => 'some-css-class'],
                'liAttr' => ['name' => 'class', 'value' => 'some-css-class'],
                'aParams' => ['href' => 'url'],
                'aAttr' => ['name' => 'href', 'value' => 'url'],
                'innerText' => 'text',
                'afterText' => 'afterText',
            ],
            [
                'liParams' => 'class="some-css-class"',
                'liAttr' => ['name' => 'class', 'value' => 'some-css-class'],
                'aParams' => 'href="url"',
                'aAttr' => ['name' => 'href', 'value' => 'url'],
                'innerText' => 'text',
                'afterText' => 'afterText',
            ]
        ];
    }
}
