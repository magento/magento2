<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\View\Asset;

use Magento\Catalog\Model\View\Asset\Placeholder;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\Asset\ContextInterface;
use Magento\Framework\View\Asset\MergeableInterface;
use Magento\Framework\View\Asset\Repository;
use Magento\Store\Model\ScopeInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PlaceholderTest extends TestCase
{
    /**
     * @var Placeholder
     */
    protected $model;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    protected $scopeConfig;

    /**
     * @var Repository|MockObject
     */
    protected $repository;

    /**
     * @var ContextInterface|MockObject
     */
    protected $imageContext;

    protected function setUp(): void
    {
        $this->scopeConfig = $this->getMockBuilder(ScopeConfigInterface::class)
            ->getMockForAbstractClass();
        $this->imageContext = $this->getMockBuilder(ContextInterface::class)
            ->getMockForAbstractClass();
        $this->repository = $this->getMockBuilder(Repository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->model = new Placeholder(
            $this->imageContext,
            $this->scopeConfig,
            $this->repository,
            'thumbnail'
        );
    }

    public function testModuleAndContentAndContentType()
    {
        $contentType = 'image';
        $this->assertEquals($contentType, $this->model->getContentType());
        $this->assertEquals($contentType, $this->model->getSourceContentType());
        $this->assertNull($this->model->getContent());
        $this->assertEquals('placeholder', $this->model->getModule());
    }

    public function testGetFilePath()
    {
        $this->assertNull($this->model->getFilePath());
        $this->scopeConfig->expects($this->once())->method('getValue')->willReturn('default/thumbnail.jpg');
        $this->assertEquals('default/thumbnail.jpg', $this->model->getFilePath());
    }

    public function testGetContext()
    {
        $this->assertInstanceOf(ContextInterface::class, $this->model->getContext());
    }

    /**
     * @param string $imageType
     * @param string $placeholderPath
     * @dataProvider getPathDataProvider
     */
    public function testGetPathAndGetSourceFile($imageType, $placeholderPath)
    {
        $imageModel = new Placeholder(
            $this->imageContext,
            $this->scopeConfig,
            $this->repository,
            $imageType
        );
        $absolutePath = '/var/www/html/magento2ce/pub/media/catalog/product';

        $this->scopeConfig->expects($this->any())
            ->method('getValue')
            ->with(
                "catalog/placeholder/{$imageType}_placeholder",
                ScopeInterface::SCOPE_STORE,
                null
            )->willReturn($placeholderPath);

        if ($placeholderPath == null) {
            $this->imageContext->expects($this->never())->method('getPath');
            $assetMock = $this->getMockBuilder(MergeableInterface::class)
                ->getMockForAbstractClass();
            $expectedResult = 'path/to_default/placeholder/by_type';
            $assetMock->expects($this->any())->method('getSourceFile')->willReturn($expectedResult);
            $this->repository->expects($this->any())->method('createAsset')->willReturn($assetMock);
        } else {
            $this->imageContext->expects($this->any())->method('getPath')->willReturn($absolutePath);
            $expectedResult = $absolutePath
                . DIRECTORY_SEPARATOR . $imageModel->getModule()
                . DIRECTORY_SEPARATOR . $placeholderPath;
        }

        $this->assertEquals($expectedResult, $imageModel->getPath());
        $this->assertEquals($expectedResult, $imageModel->getSourceFile());
    }

    /**
     * @param string $imageType
     * @param string $placeholderPath
     * @dataProvider getPathDataProvider
     */
    public function testGetUrl($imageType, $placeholderPath)
    {
        $imageModel = new Placeholder(
            $this->imageContext,
            $this->scopeConfig,
            $this->repository,
            $imageType
        );

        $this->scopeConfig->expects($this->any())
            ->method('getValue')
            ->with(
                "catalog/placeholder/{$imageType}_placeholder",
                ScopeInterface::SCOPE_STORE,
                null
            )->willReturn($placeholderPath);

        if ($placeholderPath == null) {
            $this->imageContext->expects($this->never())->method('getBaseUrl');
            $expectedResult = 'http://localhost/media/catalog/product/to_default/placeholder/by_type';
            $this->repository->expects($this->any())->method('getUrl')->willReturn($expectedResult);
        } else {
            $baseUrl = 'http://localhost/media/catalog/product';
            $this->imageContext->expects($this->any())->method('getBaseUrl')->willReturn($baseUrl);
            $expectedResult = $baseUrl
                . DIRECTORY_SEPARATOR . $imageModel->getModule()
                . DIRECTORY_SEPARATOR . $placeholderPath;
        }

        $this->assertEquals($expectedResult, $imageModel->getUrl());
    }

    /**
     * @return array
     */
    public function getPathDataProvider()
    {
        return [
            [
                'thumbnail',
                'default/thumbnail.jpg',
            ],
            [
                'non_exist',
                null,
            ],
        ];
    }
}
