<?php
/***
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Cms\Test\Unit\ViewModel\Page\Grid;

use Magento\Cms\Model\Page\TargetUrlBuilderInterface;
use Magento\Cms\ViewModel\Page\Grid\UrlBuilder;
use Magento\Framework\Url\EncoderInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class UrlBuilderTest
 *
 * Testing the UrlBuilder
 */
class UrlBuilderTest extends TestCase
{
    /**
     * @var UrlBuilder
     */
    private $viewModel;

    /**
     * @var UrlInterface|MockObject
     */
    private $frontendUrlBuilderMock;

    /**
     * @var EncoderInterface|MockObject
     */
    private $urlEncoderMock;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManagerMock;

    /**
     * @var TargetUrlBuilderInterface
     */
    private $getTargetUrlMock;

    /**
     * Set Up
     */
    protected function setUp(): void
    {
        $this->frontendUrlBuilderMock = $this->getMockBuilder(UrlInterface::class)
            ->onlyMethods(['getUrl', 'setScope'])
            ->getMockForAbstractClass();
        $this->urlEncoderMock = $this->getMockForAbstractClass(EncoderInterface::class);
        $this->storeManagerMock = $this->getMockBuilder(StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->getTargetUrlMock = $this->getMockBuilder(TargetUrlBuilderInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->viewModel = new UrlBuilder(
            $this->frontendUrlBuilderMock,
            $this->urlEncoderMock,
            $this->storeManagerMock,
            $this->getTargetUrlMock
        );
    }

    /**
     * Testing url builder with no scope provided
     *
     * @dataProvider nonScopedUrlsDataProvider
     *
     * @param array $url
     * @param string $expected
     * @param string $store
     * @param null $scope
     */
    public function testUrlBuilderWithNoScope(array $url, string $expected, string $store, $scope = null)
    {
        $this->frontendUrlBuilderMock->expects($this->any())
            ->method('getUrl')
            ->with($url['path'], $url['params'])
            ->willReturn($expected);

        $result = $this->viewModel->getUrl($url['path'], $scope, $store);

        $this->assertSame($expected, $result);
    }

    /**
     * Providing a non scoped urls
     *
     * @return array
     */
    public static function nonScopedUrlsDataProvider(): array
    {
        return [
            [
                [
                    'path' => 'test/view',
                    'params' => [
                        '_current' => false,
                        '_nosid' => true
                    ]
                ],
                'http://domain.com/test/view/',
                'en'
            ]
        ];
    }

    /**
     * Testing url builder with a scope provided
     *
     * @param array $routePaths
     * @param array $expectedUrls
     *
     * @dataProvider scopedUrlsDataProvider
     */
    public function testScopedUrlBuilder(
        array $routePaths,
        array $expectedUrls
    ) {
        /** @var StoreInterface|MockObject $storeMock */
        $storeMock = $this->getMockForAbstractClass(StoreInterface::class);
        $storeMock->expects($this->any())
            ->method('getCode')
            ->willReturn('en');
        $this->storeManagerMock->expects($this->once())
            ->method('getDefaultStoreView')
            ->willReturn($storeMock);
        $this->getTargetUrlMock->expects($this->any())
            ->method('process')
            ->willReturnCallback(function ($routePath, $locale) use ($routePaths) {
                if ($routePath == $routePaths[0] && $locale == 'en') {
                    return $routePaths[0];
                } elseif ($routePath == $routePaths[1] && $locale == 'en') {
                    return $routePaths[1];
                }
            });
        $this->frontendUrlBuilderMock->expects($this->any())
            ->method('getUrl')
            ->willReturnOnConsecutiveCalls($expectedUrls[0], $expectedUrls[1]);

        $result = $this->viewModel->getUrl($routePaths[0], 'store', 'en');

        $this->assertSame($expectedUrls[0], $result);
    }

    /**
     * Providing a scoped urls
     *
     * @return array
     */
    public static function scopedUrlsDataProvider(): array
    {
        return [
            [
                ['test1/index1', 'stores/store/switch'],
                ['http://domain.com/test1', 'http://domain.com/test1/index1']
            ],
            [
                ['fr/test2/index2', 'stores/store/switch'],
                ['http://domain.com/fr/test2', 'http://domain.com/fr/test2/index2']
            ]
        ];
    }
}
