<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Test\Unit\Controller\Adminhtml\Product\Initialization\Helper\Plugin;

use Magento\Catalog\Api\Data\ProductExtensionInterface;

/**
 * Unit tests for \Magento\Downloadable\Controller\Adminhtml\Product\Initialization\Helper\Plugin\Downloadable.
 */
class DownloadableTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Downloadable\Controller\Adminhtml\Product\Initialization\Helper\Plugin\Downloadable
     */
    private $downloadablePlugin;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\Request\Http
     */
    private $requestMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $productMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $subjectMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Catalog\Api\Data\ProductExtensionInterface
     */
    private $extensionAttributesMock;

    /**
     * @var \Magento\Downloadable\Model\Product\Type|\Magento\Catalog\Api\Data\ProductExtensionInterface
     */
    private $downloadableProductTypeMock;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->requestMock = $this->createMock(\Magento\Framework\App\Request\Http::class);
        $this->productMock = $this->createPartialMock(
            \Magento\Catalog\Model\Product::class,
            ['setDownloadableData', 'getExtensionAttributes', '__wakeup', 'getTypeInstance']
        );
        $this->subjectMock = $this->createMock(
            \Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper::class
        );
        $this->extensionAttributesMock = $this->getMockBuilder(ProductExtensionInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['setDownloadableProductSamples', 'setDownloadableProductLinks'])
            ->getMockForAbstractClass();
        $sampleFactoryMock = $this->getMockBuilder(\Magento\Downloadable\Api\Data\SampleInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $linkFactoryMock = $this->getMockBuilder(\Magento\Downloadable\Api\Data\LinkInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $linkBuilderMock = $this->getMockBuilder(\Magento\Downloadable\Model\Link\Builder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $sampleBuilderMock = $this->getMockBuilder(\Magento\Downloadable\Model\Sample\Builder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->downloadableProductTypeMock = $this->createPartialMock(
            \Magento\Downloadable\Model\Product\Type::class,
            ['getLinks', 'getSamples']
        );
        $this->downloadablePlugin =
            new \Magento\Downloadable\Controller\Adminhtml\Product\Initialization\Helper\Plugin\Downloadable(
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
