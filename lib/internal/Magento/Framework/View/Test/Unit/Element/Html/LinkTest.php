<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Test\Unit\Element\Html;

use Magento\Framework\DataObject;
use Magento\Framework\Math\Random;
use Magento\Framework\View\Helper\SecureHtmlRenderer;

/**
 * Test Link widget.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class LinkTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $objectManager;

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

    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

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
            ->disableOriginalConstructor()
            ->getMock();
        $scopeConfigMock->expects($this->any())
            ->method('isSetFlag')
            ->willReturn(true);

        $resolverMock = $this->getMockBuilder(\Magento\Framework\View\Element\Template\File\Resolver::class)
            ->setMethods([])->disableOriginalConstructor()->getMock();

        $contextMock = $this->getMockBuilder(\Magento\Framework\View\Element\Template\Context::class)
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
        $contextMock->method('getEventManager')
            ->willReturn($this->createMock(\Magento\Framework\Event\ManagerInterface::class));
        $randomMock = $this->createMock(Random::class);
        $randomMock->method('getRandomString')->willReturn('random');
        $secureRendererMock = $this->createMock(SecureHtmlRenderer::class);
        $secureRendererMock->method('renderTag')
            ->willReturnCallback(
                function (string $tag, array $attributes, string $content): string {
                    $attributes = new DataObject($attributes);

                    return "<$tag {$attributes->serialize()}>$content</$tag>";
                }
            );
        $secureRendererMock->method('renderEventListenerAsTag')
            ->willReturnCallback(
                function (string $event, string $js, string $selector): string {
                    return "<script>document.querySelector('$selector').$event = function () { $js };</script>";
                }
            );

        /** @var \Magento\Framework\View\Element\Html\Link $linkWithAttributes */
        $this->link = $this->objectManager->getObject(
            \Magento\Framework\View\Element\Html\Link::class,
            ['context' => $contextMock, 'random' => $randomMock, 'secureRenderer' => $secureRendererMock]
        );
    }

    public function testGetLinkAttributes()
    {
        $linkWithAttributes = clone $this->link;
        $this->assertEquals(
            'href="http://site.com/link.html"',
            $linkWithAttributes->getLinkAttributes()
        );

        /** @var \Magento\Framework\View\Element\Html\Link $linkWithoutAttributes */
        $linkWithoutAttributes = clone $this->link;
        foreach ($this->allowedAttributes as $attribute) {
            $linkWithoutAttributes->setDataUsingMethod($attribute, $attribute);
        }

        $this->assertEquals(
            'href="http://site.com/link.html" shape="shape" tabindex="tabindex"'
            . ' id="id"',
            $linkWithoutAttributes->getLinkAttributes()
        );
    }

    public function testLinkHtml(): void
    {
        $this->link->setDataUsingMethod('style', 'display: block;');
        $this->link->setDataUsingMethod('onclick', 'alert("clicked");');

        $html = $this->link->toHtml();
        $this->assertEquals(
            '<li><a href="http://site.com/link.html" id="idrandom" ></a></li>'
            .'<style >#idrandom { display: block; }</style>'
            .'<script>document.querySelector(\'#idrandom\').onclick = function () { alert("clicked"); };</script>',
            $html
        );
    }
}
