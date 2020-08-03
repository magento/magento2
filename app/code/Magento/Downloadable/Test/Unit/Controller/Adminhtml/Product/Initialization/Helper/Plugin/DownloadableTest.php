<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Downloadable\Test\Unit\Controller\Adminhtml\Product\Initialization\Helper\Plugin;

use Magento\Catalog\Api\Data\ProductExtensionInterface;
use Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper;
use Magento\Catalog\Model\Product;
use Magento\Downloadable\Api\Data\LinkInterfaceFactory;
use Magento\Downloadable\Api\Data\SampleInterfaceFactory;
use Magento\Downloadable\Controller\Adminhtml\Product\Initialization\Helper\Plugin\Downloadable;
use Magento\Downloadable\Model\Link\Builder;
use Magento\Downloadable\Model\Product\Type;
use Magento\Framework\App\Request\Http;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for \Magento\Downloadable\Controller\Adminhtml\Product\Initialization\Helper\Plugin\Downloadable.
 */
class DownloadableTest extends TestCase
{
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
        $this->productMock = $this->getMockBuilder(Product::class)
            ->addMethods(['setDownloadableData'])
            ->onlyMethods(['getExtensionAttributes', '__wakeup', 'getTypeInstance'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->subjectMock = $this->createMock(
            Helper::class
        );
        $this->extensionAttributesMock = $this->getMockBuilder(ProductExtensionInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['setDownloadableProductSamples', 'setDownloadableProductLinks'])
            ->getMockForAbstractClass();
        $sampleFactoryMock = $this->getMockBuilder(SampleInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $linkFactoryMock = $this->getMockBuilder(LinkInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $linkBuilderMock = $this->getMockBuilder(Builder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $sampleBuilderMock = $this->getMockBuilder(\Magento\Downloadable\Model\Sample\Builder::class)
            ->disableOriginalConstructor()
            ->getMock();
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
     * @dataProvider afterInitializeWithEmptyDataDataProvider
     */
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
    public function afterInitializeWithEmptyDataDataProvider()
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
     * @dataProvider afterInitializeIfDownloadableNotExistDataProvider
     */
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
    public function afterInitializeIfDownloadableNotExistDataProvider()
    {
        return [
            [false],
            [[]],
            [null],
        ];
    }
}
