<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CmsUrlRewrite\Test\Unit\Model\Page;

use Magento\Cms\Model\Page;
use Magento\CmsUrlRewrite\Model\CmsPageUrlPathGenerator;
use Magento\CmsUrlRewrite\Model\Page\TargetUrlBuilder;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\UrlRewrite\Model\UrlFinderInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class TargetUrlBuilderTest
 *
 * Testing the target url process successfully from the route path
 */
class TargetUrlBuilderTest extends TestCase
{
    /**
     * @var TargetUrlBuilder
     */
    private $viewModel;

    /**
     * @var UrlInterface|MockObject
     */
    private $frontendUrlBuilderMock;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManagerMock;

    /**
     * @var Page|MockObject
     */
    private $cmsPageMock;

    /**
     * @var CmsPageUrlPathGenerator|MockObject
     */
    private $cmsPageUrlPathGeneratorMock;

    /**
     * @var UrlFinderInterface|MockObject
     */
    private $urlFinderMock;

    /**
     * Set Up
     */
    protected function setUp(): void
    {
        $this->frontendUrlBuilderMock = $this->createMock(UrlInterface::class);
        $this->cmsPageMock = $this->createPartialMock(
            Page::class,
            ['checkIdentifier']
        );
        $this->cmsPageUrlPathGeneratorMock = $this->createPartialMock(
            CmsPageUrlPathGenerator::class,
            ['getCanonicalUrlPath']
        );
        $this->storeManagerMock = $this->createMock(StoreManagerInterface::class);
        $this->urlFinderMock = $this->createMock(UrlFinderInterface::class);
        $this->viewModel = new TargetUrlBuilder(
            $this->frontendUrlBuilderMock,
            $this->storeManagerMock,
            $this->cmsPageMock,
            $this->urlFinderMock,
            $this->cmsPageUrlPathGeneratorMock
        );
    }

    /**
     * Testing getTargetUrl with a scope provided
     *
     * @param array $urlParams
     * @param string $storeId
     * @throws NoSuchEntityException
     */
    #[DataProvider('scopedUrlsDataProvider')]
    public function testGetTargetUrl(array $urlParams, string $storeId): void
    {
        /** @var StoreInterface|MockObject $storeMock */
        $storeMock = $this->createMock(StoreInterface::class);
        $storeMock->expects($this->any())
            ->method('getId')
            ->willReturn($storeId);
        $this->storeManagerMock->expects($this->once())
            ->method('getStore')
            ->willReturn($storeMock);

        $this->cmsPageMock->expects($this->any())
            ->method('checkIdentifier')
            ->willReturn("1");
        $this->cmsPageUrlPathGeneratorMock->expects($this->any())
            ->method('getCanonicalUrlPath')
            ->with($this->cmsPageMock)
            ->willReturn('test/index');
        $this->urlFinderMock->expects($this->any())
            ->method('findOneByData')
            ->willReturn('test/index');
        $this->frontendUrlBuilderMock->expects($this->any())
            ->method('getUrl')
            ->willReturnCallback(function (...$args) use ($storeId, $urlParams) {
                static $callCount = 0;
                $callCount++;
                switch ($callCount) {
                    case 1:
                        if ($args === ['test/index',
                            ['_current' => false, '_nosid' => true, '_query' =>
                                [StoreManagerInterface::PARAM_NAME => $storeId]]]) {
                            return 'http://domain.com/test';
                        }
                        break;
                    case 2:
                        if ($args === ['stores/store/switch', $urlParams]) {
                            return 'http://domain.com/test/index';
                        }
                        break;

                }
            });

        $result = $this->viewModel->process('test/index', $storeId);

        $this->assertSame('http://domain.com/test', $result);
    }

    /**
     * Providing a scoped urls
     *
     * @return array
     */
    public static function scopedUrlsDataProvider(): array
    {
        $enStoreCode = 'en';
        $defaultUrlParams = [
            '_current' => false,
            '_nosid' => true,
            '_query' => [
                '___store' => $enStoreCode,
                'uenc' => null,
            ]
        ];

        return [
            [
                $defaultUrlParams,
                "1"
            ],
            [
                $defaultUrlParams,
                "2"
            ]
        ];
    }
}
