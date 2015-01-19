<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
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
        'some_invalid_data',
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
                'expected' => 'shape="shape" tabindex="tabindex" onfocus="onfocus" onblur="onblur" id="id"',
            ],
            'empty' => [
                'link' => $linkWithoutAttributes,
                'expected' => '',
            ],
        ];
    }
}
