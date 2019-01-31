<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\UrlRewrite\Test\Unit\Model\Exception;

use Magento\UrlRewrite\Model\Exception\UrlAlreadyExistsException;
use Magento\Framework\Phrase;

/**
 * Class UrlAlreadyExistsExceptionTest
 */
class UrlAlreadyExistsExceptionTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Framework\Phrase\RendererInterface */
    private $defaultRenderer;

    /** @var string */
    private $renderedMessage;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->defaultRenderer = \Magento\Framework\Phrase::getRenderer();
        $rendererMock = $this->getMockBuilder(\Magento\Framework\Phrase\Renderer\Placeholder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->renderedMessage = 'rendered message';
        $rendererMock->expects($this->once())
            ->method('render')
            ->will($this->returnValue($this->renderedMessage));
        \Magento\Framework\Phrase::setRenderer($rendererMock);
    }

    /**
     * @return void
     */
    public function tearDown()
    {
        \Magento\Framework\Phrase::setRenderer($this->defaultRenderer);
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
