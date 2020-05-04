<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\View\Test\Unit\Element\Html;

use Magento\Framework\App\Config;
use Magento\Framework\Escaper;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Html\Link;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\View\Element\Template\File\Resolver;
use Magento\Framework\View\Element\Template\File\Validator;
use PHPUnit\Framework\TestCase;

class LinkTest extends TestCase
{
    private $objectManager;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
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
     * @var Link
     */
    protected $link;

    public function testGetLinkAttributes()
    {
        $escaperMock = $this->getMockBuilder(Escaper::class)
            ->setMethods(['escapeHtml'])->disableOriginalConstructor()
            ->getMock();

        $escaperMock->expects($this->any())
            ->method('escapeHtml')
            ->willReturnArgument(0);

        $urlBuilderMock = $this->getMockBuilder(UrlInterface::class)
            ->setMethods(['getUrl'])->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $urlBuilderMock->expects($this->any())
            ->method('getUrl')
            ->willReturn('http://site.com/link.html');

        $validtorMock = $this->getMockBuilder(Validator::class)
            ->setMethods(['isValid'])->disableOriginalConstructor()
            ->getMock();
        $validtorMock->expects($this->any())
            ->method('isValid')
            ->willReturn(false);

        $scopeConfigMock = $this->getMockBuilder(Config::class)
            ->setMethods(['isSetFlag'])->disableOriginalConstructor()
            ->getMock();
        $scopeConfigMock->expects($this->any())
            ->method('isSetFlag')
            ->willReturn(true);

        $resolverMock = $this->getMockBuilder(Resolver::class)
            ->setMethods([])->disableOriginalConstructor()
            ->getMock();

        $contextMock = $this->getMockBuilder(Context::class)
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

        /** @var Link $linkWithAttributes */
        $linkWithAttributes = $this->objectManager->getObject(
            Link::class,
            ['context' => $contextMock]
        );

        $this->assertEquals(
            'href="http://site.com/link.html"',
            $linkWithAttributes->getLinkAttributes()
        );

        /** @var Link $linkWithoutAttributes */
        $linkWithoutAttributes = $this->objectManager->getObject(
            Link::class,
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
