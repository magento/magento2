<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Downloadable\Test\Unit\Controller\Adminhtml\Product\Initialization\Helper\Plugin;

use PHPUnit\Framework\Attributes\DataProvider;
use Magento\Catalog\Api\Data\ProductExtensionInterface;
use Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper;
use Magento\Catalog\Model\Product;
use Magento\Downloadable\Api\Data\LinkInterfaceFactory;
use Magento\Downloadable\Api\Data\SampleInterfaceFactory;
use Magento\Downloadable\Controller\Adminhtml\Product\Initialization\Helper\Plugin\Downloadable;
use Magento\Downloadable\Model\Link\Builder;
use Magento\Downloadable\Model\Product\Type;
use Magento\Framework\App\Request\Http;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for \Magento\Downloadable\Controller\Adminhtml\Product\Initialization\Helper\Plugin\Downloadable.
 */
class DownloadableTest extends TestCase
{
    use MockCreationTrait;
    /**
     * @var Downloadable
     */
    private $downloadablePlugin;

    /**
     * @var MockObject|Http
     */
    private $requestMock;

    /**
     * @var MockObject
     */
    private $productMock;

    /**
     * @var MockObject
     */
    private $subjectMock;

    /**
     * @var MockObject|ProductExtensionInterface
     */
    private $extensionAttributesMock;

    /**
     * @var Type|ProductExtensionInterface
     */
    private $downloadableProductTypeMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->requestMock = $this->createMock(Http::class);
        $this->productMock = $this->createPartialMockWithReflection(
            Product::class,
            [
                'setDownloadableData',
                'getExtensionAttributes',
                '__wakeup',
                'getTypeInstance'
            ]
        );
        $this->subjectMock = $this->createMock(
            Helper::class
        );
        $this->extensionAttributesMock = $this->createPartialMockWithReflection(
            ProductExtensionInterface::class,
            [
                'setDownloadableProductLinks',
                'setDownloadableProductSamples'
            ]
        );
        $sampleFactoryMock = $this->createPartialMock(SampleInterfaceFactory::class, ['create']);
        $linkFactoryMock = $this->createPartialMock(LinkInterfaceFactory::class, ['create']);
        $linkBuilderMock = $this->createMock(Builder::class);
        $sampleBuilderMock = $this->createMock(\Magento\Downloadable\Model\Sample\Builder::class);
        $this->downloadableProductTypeMock = $this->createPartialMock(
            Type::class,
            ['getLinks', 'getSamples']
        );
        $this->downloadablePlugin =
            new Downloadable(
                $this->requestMock,
                $linkBuilderMock,
                $sampleBuilderMock,
                $sampleFactoryMock,
                $linkFactoryMock
            );
    }

    /**
     * @param array $downloadable
     */
    #[DataProvider('afterInitializeWithEmptyDataDataProvider')]
    public function testAfterInitializeWithNoDataToSave($downloadable)
    {
        $this->requestMock->expects($this->once())
            ->method('getPost')
            ->with('downloadable')
            ->willReturn($downloadable);
        $this->productMock->expects($this->once())->method('setDownloadableData')->with($downloadable);
        $this->productMock->expects($this->once())
            ->method('getExtensionAttributes')
            ->willReturn($this->extensionAttributesMock);
        $this->productMock->expects($this->exactly(2))
            ->method('getTypeInstance')
            ->willReturn($this->downloadableProductTypeMock);
        $this->downloadableProductTypeMock->expects($this->once())->method('getLinks')->willReturn([]);
        $this->downloadableProductTypeMock->expects($this->once())->method('getSamples')->willReturn([]);
        $this->extensionAttributesMock->expects($this->once())
            ->method('setDownloadableProductLinks')
            ->with([]);
        $this->extensionAttributesMock->expects($this->once())
            ->method('setDownloadableProductSamples')
            ->with([]);
        $this->productMock->expects($this->once())
            ->method('getExtensionAttributes')
            ->willReturn($this->extensionAttributesMock);

        $this->downloadablePlugin->afterInitialize($this->subjectMock, $this->productMock);
    }

    /**
     * @return array
     */
    public static function afterInitializeWithEmptyDataDataProvider()
    {
        return [
            [['link' => [], 'sample' => []]],
            [
                [
                    'link' => [
                        ['is_delete' => 1, 'link_type' => 'url'],
                        ['is_delete' => 1, 'link_type' => 'file'],
                        []
                    ],
                    'sample' => [
                        ['is_delete' => 1, 'sample_type' => 'url'],
                        ['is_delete' => 1, 'sample_type' => 'file'],
                        []
                    ]
                ]
            ],
        ];
    }

    /**
     * @param mixed $downloadable
     */
    #[DataProvider('afterInitializeIfDownloadableNotExistDataProvider')]
    public function testAfterInitializeIfDownloadableNotExist($downloadable)
    {
        $this->requestMock->expects($this->once())
            ->method('getPost')
            ->with('downloadable')
            ->willReturn($downloadable);
        $this->productMock->expects($this->never())->method('setDownloadableData');
        $this->downloadablePlugin->afterInitialize($this->subjectMock, $this->productMock);
    }

    /**
     * @return array
     */
    public static function afterInitializeIfDownloadableNotExistDataProvider()
    {
        return [
            [false],
            [[]],
            [null],
        ];
    }
}
