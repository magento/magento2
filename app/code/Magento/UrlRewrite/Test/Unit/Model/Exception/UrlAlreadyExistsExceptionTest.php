<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\UrlRewrite\Test\Unit\Model\Exception;

use Magento\Framework\Phrase;
use Magento\Framework\Phrase\Renderer\Placeholder;
use Magento\Framework\Phrase\RendererInterface;
use Magento\UrlRewrite\Model\Exception\UrlAlreadyExistsException;
use PHPUnit\Framework\TestCase;

class UrlAlreadyExistsExceptionTest extends TestCase
{
    /** @var RendererInterface */
    private $defaultRenderer;

    /** @var string */
    private $renderedMessage;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->defaultRenderer = Phrase::getRenderer();
        $rendererMock = $this->getMockBuilder(Placeholder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->renderedMessage = 'rendered message';
        $rendererMock->expects($this->once())
            ->method('render')
            ->willReturn($this->renderedMessage);
        Phrase::setRenderer($rendererMock);
    }

    /**
     * @return void
     */
    protected function tearDown(): void
    {
        Phrase::setRenderer($this->defaultRenderer);
    }

    public function testUrls()
    {
        $expectedCode = 42;
        $urls = ['someUrl.html'];
        $localizedException = new UrlAlreadyExistsException(
            new Phrase("message %1", ['test']),
            new \Exception(),
            $expectedCode,
            $urls
        );

        $this->assertEquals($urls, $localizedException->getUrls());
    }

    public function testDefaultPhrase()
    {
        $localizedException = new UrlAlreadyExistsException();

        $this->assertEquals(
            'rendered message',
            $localizedException->getMessage()
        );
    }
}
