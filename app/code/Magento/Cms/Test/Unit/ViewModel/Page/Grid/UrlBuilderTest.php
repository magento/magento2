<?php
/***
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Cms\Test\Unit\ViewModel\Page\Grid;

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
     * Set Up
     */
    protected function setUp(): void
    {
        $this->frontendUrlBuilderMock = $this->getMockBuilder(UrlInterface::class)
            ->setMethods(['getUrl', 'setScope'])
            ->getMockForAbstractClass();
        $this->urlEncoderMock = $this->getMockForAbstractClass(EncoderInterface::class);
        $this->storeManagerMock = $this->getMockBuilder(StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->viewModel = new UrlBuilder(
            $this->frontendUrlBuilderMock,
            $this->urlEncoderMock,
            $this->storeManagerMock
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
    public function nonScopedUrlsDataProvider(): array
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
     * @dataProvider scopedUrlsDataProvider
     *
     * @param string $storeCode
     * @param string $defaultStoreCode
     * @param array $urlParams
     * @param string $scope
     */
    public function testScopedUrlBuilder(
        string $storeCode,
        string $defaultStoreCode,
        array $urlParams,
        string $scope = 'store'
    ) {
        /** @var StoreInterface|MockObject $storeMock */
        $storeMock = $this->getMockForAbstractClass(StoreInterface::class);
        $storeMock->expects($this->any())
            ->method('getCode')
            ->willReturn($defaultStoreCode);
        $this->storeManagerMock->expects($this->once())
            ->method('getDefaultStoreView')
            ->willReturn($storeMock);

        $this->frontendUrlBuilderMock->expects($this->any())
            ->method('getUrl')
            ->withConsecutive(
                [
                    'test/index',
                    [
                        '_current' => false,
                        '_nosid' => true,
                        '_query' => [
                            StoreManagerInterface::PARAM_NAME => $storeCode
                        ]
                    ]
                ],
                [
                    'stores/store/switch',
                    $urlParams
                ]
            )
            ->willReturnOnConsecutiveCalls(
                'http://domain.com/test',
                'http://domain.com/test/index'
            );

        $result = $this->viewModel->getUrl('test/index', $scope, $storeCode);

        $this->assertSame('http://domain.com/test/index', $result);
    }

    /**
     * Providing a scoped urls
     *
     * @return array
     */
    public function scopedUrlsDataProvider(): array
    {
        $enStoreCode = 'en';
        $frStoreCode = 'fr';
        $scopedDefaultUrlParams = $defaultUrlParams = [
            '_current' => false,
            '_nosid' => true,
            '_query' => [
                '___store' => $enStoreCode,
                'uenc' => null,
            ]
        ];
        $scopedDefaultUrlParams['_query']['___from_store'] = $frStoreCode;

        return [
            [
                $enStoreCode,
                $enStoreCode,
                $defaultUrlParams,
            ],
            [
                $enStoreCode,
                $frStoreCode,
                $scopedDefaultUrlParams
            ]
        ];
    }
}
