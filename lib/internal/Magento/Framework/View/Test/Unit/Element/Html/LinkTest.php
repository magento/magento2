<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Test\Unit\Element\Html;

class LinkTest extends \PHPUnit\Framework\TestCase
{
    private $objectManager;

    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
    }

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

    public function testGetLinkAttributes()
    {
        $escaperMock = $this->getMockBuilder(\Magento\Framework\Escaper::class)
            ->setMethods(['escapeHtml'])->disableOriginalConstructor()->getMock();

        $escaperMock->expects($this->any())
            ->method('escapeHtml')
            ->will($this->returnArgument(0));

        $urlBuilderMock = $this->getMockBuilder(\Magento\Framework\UrlInterface::class)
            ->setMethods(['getUrl'])->disableOriginalConstructor()->getMockForAbstractClass();

        $urlBuilderMock->expects($this->any())
            ->method('getUrl')
            ->willReturn('http://site.com/link.html');

        $validtorMock = $this->getMockBuilder(\Magento\Framework\View\Element\Template\File\Validator::class)
            ->setMethods(['isValid'])->disableOriginalConstructor()->getMock();
        $validtorMock->expects($this->any())
            ->method('isValid')
            ->willReturn(false);

        $scopeConfigMock = $this->getMockBuilder(\Magento\Framework\App\Config::class)
            ->setMethods(['isSetFlag'])->disableOriginalConstructor()->getMock();
        $scopeConfigMock->expects($this->any())
            ->method('isSetFlag')
            ->willReturn(true);

        $resolverMock = $this->getMockBuilder(\Magento\Framework\View\Element\Template\File\Resolver::class)
            ->setMethods([])->disableOriginalConstructor()->getMock();

        $contextMock = $this->getMockBuilder(\Magento\Framework\View\Element\Template\Context::class)
            ->setMethods(['getEscaper', 'getUrlBuilder', 'getValidator', 'getResolver', 'getScopeConfig'])
            ->disableOriginalConstructor()
            ->getMock();

        $contextMock->expects($this->any())
            ->method('getValidator')
            ->willReturn($validtorMock);

        $contextMock->expects($this->any())
            ->method('getResolver')
            ->willReturn($resolverMock);

        $contextMock->expects($this->any())
            ->method('getEscaper')
            ->willReturn($escaperMock);

        $contextMock->expects($this->any())
            ->method('getUrlBuilder')
            ->willReturn($urlBuilderMock);

        $contextMock->expects($this->any())
            ->method('getScopeConfig')
            ->willReturn($scopeConfigMock);

        /** @var \Magento\Framework\View\Element\Html\Link $linkWithAttributes */
        $linkWithAttributes = $this->objectManager->getObject(
            \Magento\Framework\View\Element\Html\Link::class,
            ['context' => $contextMock]
        );

        $this->assertEquals(
            'href="http://site.com/link.html"',
            $linkWithAttributes->getLinkAttributes()
        );

        /** @var \Magento\Framework\View\Element\Html\Link $linkWithoutAttributes */
        $linkWithoutAttributes = $this->objectManager->getObject(
            \Magento\Framework\View\Element\Html\Link::class,
            ['context' => $contextMock]
        );
        foreach ($this->allowedAttributes as $attribute) {
            $linkWithoutAttributes->setDataUsingMethod($attribute, $attribute);
        }

        $this->assertEquals(
            'href="http://site.com/link.html" shape="shape" tabindex="tabindex"'
            . ' onfocus="onfocus" onblur="onblur" id="id"',
            $linkWithoutAttributes->getLinkAttributes()
        );
    }
}
