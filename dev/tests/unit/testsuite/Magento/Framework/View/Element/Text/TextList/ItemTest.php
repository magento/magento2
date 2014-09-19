<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test for view BlockPool model
 */
namespace Magento\Framework\View\Element\Text\TextList;

class ItemTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Item
     */
    protected $item;

    protected function setUp()
    {
        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
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
            'content' => $innerText
        ], $this->item->toHtml());
    }

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
