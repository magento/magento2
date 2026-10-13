<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\View\Test\Unit\Element\Html\Link;

use Magento\Framework\App\Request\Http;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Html\Link\Current;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

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
            ->willReturn($urlStub);

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
     * @return void     */
    #[DataProvider('isCurrentDataProvider')]
    public function testIsCurrent($pathStub, $urlStub, $request, $expected): void
    {
        $this->_requestMock->expects($this->any())
            ->method('getPathInfo')
            ->willReturn($request['pathInfoStub']);
        $this->_requestMock->expects($this->any())
            ->method('getModuleName')
            ->willReturn($request['moduleStub']);
        $this->_requestMock->expects($this->any())
            ->method('getControllerName')
            ->willReturn($request['controllerStub']);
        $this->_requestMock->expects($this->any())
            ->method('getActionName')
            ->willReturn($request['actionStub']);

        $withArgs = $willReturnArgs = [];

        $withArgs[] = [$pathStub];
        $willReturnArgs[] = $urlStub;
        $withArgs[] = [$request['mcaStub']];
        $willReturnArgs[] = $request['getUrl'];
        $withArgs[] = ['*/*/*', ['_current' => false, '_use_rewrite' => true]];

        if ($request['mcaStub'] == '') {
            $willReturnArgs[] = $urlStub;
        } else {
            $willReturnArgs[] = '';
        }
        $this->_urlBuilderMock
            ->method('getUrl')
            ->willReturnCallback(function ($arg) use ($withArgs, $willReturnArgs) {
                static $callCount = 0;
                $currentWithArg = $withArgs[$callCount];
                $currentReturnArg = (array) $willReturnArgs[$callCount];
                $callCount++;
                if ($arg == $currentWithArg[0]) {
                    return  $currentReturnArg;
                }
            });

        $this->currentLink->setPath($pathStub);
        $this->assertEquals($expected, $this->currentLink->isCurrent());
    }

    /**
     * Data provider for is current.
     *
     * @return array
     */
    public static function isCurrentDataProvider(): array
    {
        return [
            'url with MCA' => [
                'pathStub' => 'test/path',
                'urlStub' => 'http://example.com/asdasd',
                'request' => [
                    'pathInfoStub' => '/test/index/',
                    'moduleStub' => 'test',
                    'controllerStub' => 'index',
                    'actionStub' => 'index',
                    'mcaStub' => 'test/index',
                    'getUrl' => 'http://example.com/asdasd/'
                ],
                'expected' => true
            ],
            'url with CMS' => [
                'pathStub' => 'test',
                'urlStub' => 'http://example.com/test',
                'request' => [
                    'pathInfoStub' => '//test//',
                    'moduleStub' => 'cms',
                    'controllerStub' => 'page',
                    'actionStub' => 'view',
                    'mcaStub' => '',
                    'getUrl' => 'http://example.com/'
                ],
                'expected' => true
            ],
            'Test if is current false' => [
                'pathStub' => 'test/path',
                'urlStub' => 'http://example.com/tests',
                'request' => [
                    'pathInfoStub' => '/test/index/',
                    'moduleStub' => 'test',
                    'controllerStub' => 'index',
                    'actionStub' => 'index',
                    'mcaStub' => 'test/index',
                    'getUrl' => 'http://example.com/asdasd/'
                ],
                'expected' => false
            ]
        ];
    }
}
