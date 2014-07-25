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

class LinkTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Link
     */
    protected $link;

    protected function setUp()
    {
        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->link = $objectManager->getObject('Magento\Framework\View\Element\Text\TextList\Link');
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
        $this->link->setLink($liParams, $aParams, $innerText, $afterText);
        $this->assertTag([
            'tag' => 'li',
            'attributes' => [$liAttr['name'] => $liAttr['value']],
            'child' => [
                'tag' => 'a',
                'attributes' => [$aAttr['name'] => $aAttr['value']],
                'content' => $innerText
            ],
            'content' => $afterText
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
