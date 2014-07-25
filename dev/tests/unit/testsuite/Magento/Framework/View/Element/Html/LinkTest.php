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
namespace Magento\Framework\View\Element\Html;

class LinkTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var array
     */
    protected $allowedAttributes = [
        'shape',
        'tabindex',
        'onfocus',
        'onblur',
        'id',
        'some_invalid_data'
    ];

    /**
     * @var \Magento\Framework\View\Element\Html\Link
     */
    protected $link;

    /**
     * @param \Magento\Framework\View\Element\Html\Link $link
     * @param string $expected
     *
     * @dataProvider getLinkAttributesDataProvider
     */
    public function testGetLinkAttributes($link, $expected)
    {
        $this->assertEquals($expected, $link->getLinkAttributes());
    }

    public function getLinkAttributesDataProvider()
    {
        $objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);

        $escaperMock = $this->getMockBuilder('Magento\Framework\Escaper')
            ->setMethods(['escapeHtml'])->disableOriginalConstructor()->getMock();

        $escaperMock->expects($this->any())
            ->method('escapeHtml')
            ->will($this->returnArgument(0));

        $urlBuilderMock = $this->getMockBuilder('Magento\Framework\UrlInterface')
            ->setMethods(['getUrl'])->disableOriginalConstructor()->getMockForAbstractClass();

        $urlBuilderMock->expects($this->any())
            ->method('getUrl')
            ->will($this->returnArgument('http://site.com/link.html'));

        $contextMock = $this->getMockBuilder('Magento\Framework\View\Element\Template\Context')
            ->setMethods(['getEscaper', 'getUrlBuilder'])->disableOriginalConstructor()->getMock();

        $contextMock->expects($this->any())
            ->method('getEscaper')
            ->will($this->returnValue($escaperMock));

        $contextMock->expects($this->any())
            ->method('getUrlBuilder')
            ->will($this->returnValue($urlBuilderMock));

        /** @var \Magento\Framework\View\Element\Html\Link $linkWithAttributes */
        $linkWithAttributes = $objectManagerHelper->getObject(
            'Magento\Framework\View\Element\Html\Link',
            ['context' => $contextMock]
        );
        /** @var \Magento\Framework\View\Element\Html\Link $linkWithoutAttributes */
        $linkWithoutAttributes = $objectManagerHelper->getObject(
            'Magento\Framework\View\Element\Html\Link',
            ['context' => $contextMock]
        );

        foreach ($this->allowedAttributes as $attribute) {
            $linkWithAttributes->setDataUsingMethod($attribute, $attribute);
        }

        return [
            'full' => [
                'link' => $linkWithAttributes,
                'expected' => 'shape="shape" tabindex="tabindex" onfocus="onfocus" onblur="onblur" id="id"'
            ],
            'empty' => [
                'link' => $linkWithoutAttributes,
                'expected' => ''
            ],
        ];
    }
}
