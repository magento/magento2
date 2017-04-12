<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Test\Unit\Controller\Adminhtml\Product\Initialization\Helper\Plugin;

use Magento\Catalog\Api\Data\ProductExtensionInterface;

class DownloadableTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Downloadable\Controller\Adminhtml\Product\Initialization\Helper\Plugin\Downloadable
     */
    protected $downloadablePlugin;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\Request\Http
     */
    protected $requestMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $subjectMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Catalog\Api\Data\ProductExtensionInterface
     */
    protected $extensionAttributesMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Downloadable\Model\SampleFactory
     */
    protected $sampleFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelperMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Downloadable\Model\linkFactory
     */
    protected $linkFactory;

    protected function setUp()
    {
        $this->jsonHelperMock = $this->getMock(\Magento\Framework\Json\Helper\Data::class, [], [], '', false);
        $this->requestMock = $this->getMock(\Magento\Framework\App\Request\Http::class, [], [], '', false);
        $this->productMock = $this->getMock(
            \Magento\Catalog\Model\Product::class,
            ['setDownloadableData', 'getExtensionAttributes', '__wakeup'],
            [],
            '',
            false
        );
        $this->subjectMock = $this->getMock(
            \Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper::class,
            [],
            [],
            '',
            false
        );
        $this->extensionAttributesMock = $this->getMockBuilder(ProductExtensionInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['setDownloadableProductSamples', 'setDownloadableProductLinks'])
            ->getMockForAbstractClass();
        $this->sampleFactoryMock = $this->getMockBuilder(\Magento\Downloadable\Api\Data\SampleInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->linkFactoryMock = $this->getMockBuilder(\Magento\Downloadable\Api\Data\LinkInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->downloadablePlugin =
            new \Magento\Downloadable\Controller\Adminhtml\Product\Initialization\Helper\Plugin\Downloadable(
                $this->requestMock,
                $this->sampleFactoryMock,
                $this->linkFactoryMock,
                $this->jsonHelperMock
            );
    }

    /**
     * @dataProvider afterInitializeWithEmptyDataDataProvider
     * @param array $downloadable
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
     * @dataProvider afterInitializeIfDownloadableNotExistDataProvider
     * @param mixed $downloadable
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
