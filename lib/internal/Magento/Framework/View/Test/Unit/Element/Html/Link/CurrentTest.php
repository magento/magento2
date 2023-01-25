<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\View\Test\Unit\Element\Html\Link;

use Magento\Framework\App\Request\Http;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Html\Link\Current;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Magento\Framework\View\Element\Html\Link\Current
 */
class CurrentTest extends TestCase
{
    /**
     * @var UrlInterface|MockObject
     */
    private $_urlBuilderMock;

    /**
     * @var Http|MockObject
     */
    private $_requestMock;

    /**
     * @var Current
     */
    private $currentLink;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->_urlBuilderMock = $this->createMock(UrlInterface::class);
        $this->_requestMock = $this->createMock(Http::class);

        $this->currentLink = (new ObjectManager($this))->getObject(
            Current::class,
            [
                'urlBuilder' => $this->_urlBuilderMock,
                'request' => $this->_requestMock
            ]
        );
    }

    /**
     * Test get Url.
     *
     * @return void
     */
    public function testGetUrl(): void
    {
        $pathStub = 'test/path';
        $urlStub = 'http://example.com/asdasd';

        $this->_urlBuilderMock->expects($this->once())
            ->method('getUrl')
            ->with($pathStub)
            ->will($this->returnValue($urlStub));

        $this->currentLink->setPath($pathStub);

        $this->assertEquals($urlStub, $this->currentLink->getHref());
    }

    /**
     * Test if set current.
     *
     * @return void
     */
    public function testIsCurrentIfIsset(): void
    {
        $pathStub = '';
        $this->_urlBuilderMock->method('getUrl')
            ->with($pathStub)
            ->willReturn('http://example.com/');
        $this->currentLink->setPath($pathStub);
        $this->currentLink->setCurrent(true);
        $this->assertTrue($this->currentLink->isCurrent());
    }

    /**
     * Test if the current url is the same as link path.
     *
     * @param string $pathStub
     * @param string $urlStub
     * @param array $request
     * @param bool $expected
     *
     * @return void
     * @dataProvider isCurrentDataProvider
     */
    public function testIsCurrent($pathStub, $urlStub, $request, $expected): void
    {
        $this->_requestMock->expects($this->any())
            ->method('getPathInfo')
            ->will($this->returnValue($request['pathInfoStub']));
        $this->_requestMock->expects($this->any())
            ->method('getModuleName')
            ->will($this->returnValue($request['moduleStub']));
        $this->_requestMock->expects($this->any())
            ->method('getControllerName')
            ->will($this->returnValue($request['controllerStub']));
        $this->_requestMock->expects($this->any())
            ->method('getActionName')
            ->will($this->returnValue($request['actionStub']));

        $withArgs = $willReturnArgs = [];

        $withArgs[] = [$pathStub];
        $willReturnArgs[] = $this->returnValue($urlStub);
        $withArgs[] = [$request['mcaStub']];
        $willReturnArgs[] = $this->returnValue($request['getUrl']);
        $withArgs[] = ['*/*/*', ['_current' => false, '_use_rewrite' => true]];

        if ($request['mcaStub'] == '') {
            $willReturnArgs[] = $this->returnValue($urlStub);
        } else {
            $willReturnArgs[] = $this->returnValue('');
        }
        $this->_urlBuilderMock
            ->method('getUrl')
            ->withConsecutive(...$withArgs)
            ->willReturnOnConsecutiveCalls(...$willReturnArgs);

        $this->currentLink->setPath($pathStub);
        $this->assertEquals($expected, $this->currentLink->isCurrent());
    }

    /**
     * Data provider for is current.
     *
     * @return array
     */
    public function isCurrentDataProvider(): array
    {
        return [
            'url with MCA' => [
                'pathStub' => 'test/path',
                'urlStub' => 'http://example.com/asdasd',
                'requestStub' => [
                    'pathInfoStub' => '/test/index/',
                    'moduleStub' => 'test',
                    'controllerStub' => 'index',
                    'actionStub' => 'index',
                    'mcaStub' => 'test/index',
                    'getUrl' => 'http://example.com/asdasd/'
                ],
                'excepted' => true
            ],
            'url with CMS' => [
                'pathStub' => 'test',
                'urlStub' => 'http://example.com/test',
                'requestStub' => [
                    'pathInfoStub' => '//test//',
                    'moduleStub' => 'cms',
                    'controllerStub' => 'page',
                    'actionStub' => 'view',
                    'mcaStub' => '',
                    'getUrl' => 'http://example.com/'
                ],
                'excepted' => true
            ],
            'Test if is current false' => [
                'pathStub' => 'test/path',
                'urlStub' => 'http://example.com/tests',
                'requestStub' => [
                    'pathInfoStub' => '/test/index/',
                    'moduleStub' => 'test',
                    'controllerStub' => 'index',
                    'actionStub' => 'index',
                    'mcaStub' => 'test/index',
                    'getUrl' => 'http://example.com/asdasd/'
                ],
                'excepted' => false
            ]
        ];
    }
}
