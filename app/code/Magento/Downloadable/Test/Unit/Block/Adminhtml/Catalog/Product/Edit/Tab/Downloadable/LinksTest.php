<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Test\Unit\Block\Adminhtml\Catalog\Product\Edit\Tab\Downloadable;

/**
 * Class LinksTest
 *
 * @package Magento\Downloadable\Test\Unit\Block\Adminhtml\Catalog\Product\Edit\Tab\Downloadable
 *
 * @deprecated
 * @see \Magento\Downloadable\Ui\DataProvider\Product\Form\Modifier\Links
 */
class LinksTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Downloadable\Block\Adminhtml\Catalog\Product\Edit\Tab\Downloadable\Links
     */
    protected $block;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $productModel;

    /**
     * @var \Magento\Downloadable\Model\Product\Type
     */
    protected $downloadableProductModel;

    /**
     * @var \Magento\Downloadable\Model\Link
     */
    protected $downloadableLinkModel;

    /**
     * @var \Magento\Framework\Escaper
     */
    protected $escaper;

    /**
     * @var \Magento\Downloadable\Helper\File
     */
    protected $fileHelper;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @var \Magento\Backend\Model\Url
     */
    protected $urlBuilder;

    protected function setUp(): void
    {
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->urlBuilder = $this->createPartialMock(\Magento\Backend\Model\Url::class, ['getUrl']);
        $attributeFactory = $this->createMock(\Magento\Eav\Model\Entity\AttributeFactory::class);
        $urlFactory = $this->createMock(\Magento\Backend\Model\UrlFactory::class);
        $this->fileHelper = $this->createPartialMock(\Magento\Downloadable\Helper\File::class, [
                'getFilePath',
                'ensureFileInFilesystem',
                'getFileSize'
            ]);
        $this->productModel = $this->createPartialMock(\Magento\Catalog\Model\Product::class, [
                '__wakeup',
                'getTypeId',
                'getTypeInstance',
                'getStoreId'
            ]);
        $this->downloadableProductModel = $this->createPartialMock(\Magento\Downloadable\Model\Product\Type::class, [
                '__wakeup',
                'getLinks'
            ]);
        $this->downloadableLinkModel = $this->createPartialMock(\Magento\Downloadable\Model\Link::class, [
                '__wakeup',
                'getId',
                'getTitle',
                'getPrice',
                'getNumberOfDownloads',
                'getLinkUrl',
                'getLinkType',
                'getSampleFile',
                'getSampleType',
                'getSortOrder',
                'getLinkFile',
                'getStoreTitle'
            ]);

        $this->coreRegistry = $this->createPartialMock(\Magento\Framework\Registry::class, [
                '__wakeup',
                'registry'
            ]);

        $this->escaper = $this->createPartialMock(\Magento\Framework\Escaper::class, ['escapeHtml']);

        $this->block = $objectManagerHelper->getObject(
            \Magento\Downloadable\Block\Adminhtml\Catalog\Product\Edit\Tab\Downloadable\Links::class,
            [
                'urlBuilder' => $this->urlBuilder,
                'attributeFactory' => $attributeFactory,
                'urlFactory' => $urlFactory,
                'coreRegistry' => $this->coreRegistry,
                'escaper' => $this->escaper,
                'downloadableFile' => $this->fileHelper
            ]
        );
    }

    /**
     * Test that getConfig method retrieve \Magento\Framework\DataObject object
     */
    public function testGetConfig()
    {
        $this->assertInstanceOf(\Magento\Framework\DataObject::class, $this->block->getConfig());
    }

    public function testGetLinkData()
    {
        $expectingFileData = [
            'file' => [
                'file' => 'file/link.gif',
                'name' => '<a href="final_url">link.gif</a>',
                'size' => '1.1',
                'status' => 'old',
            ],
            'sample_file' => [
                'file' => 'file/sample.gif',
                'name' => '<a href="final_url">sample.gif</a>',
                'size' => '1.1',
                'status' => 'old',
            ],
        ];

        $this->productModel->expects($this->any())->method('getTypeId')
            ->willReturn('downloadable');
        $this->productModel->expects($this->any())->method('getTypeInstance')
            ->willReturn($this->downloadableProductModel);
        $this->productModel->expects($this->any())->method('getStoreId')
            ->willReturn(0);
        $this->downloadableProductModel->expects($this->any())->method('getLinks')
            ->willReturn([$this->downloadableLinkModel]);
        $this->coreRegistry->expects($this->any())->method('registry')
            ->willReturn($this->productModel);
        $this->downloadableLinkModel->expects($this->any())->method('getId')
            ->willReturn(1);
        $this->downloadableLinkModel->expects($this->any())->method('getTitle')
            ->willReturn('Link Title');
        $this->downloadableLinkModel->expects($this->any())->method('getPrice')
            ->willReturn('10');
        $this->downloadableLinkModel->expects($this->any())->method('getNumberOfDownloads')
            ->willReturn('6');
        $this->downloadableLinkModel->expects($this->any())->method('getLinkUrl')
            ->willReturn(null);
        $this->downloadableLinkModel->expects($this->any())->method('getLinkType')
            ->willReturn('file');
        $this->downloadableLinkModel->expects($this->any())->method('getSampleFile')
            ->willReturn('file/sample.gif');
        $this->downloadableLinkModel->expects($this->any())->method('getSampleType')
            ->willReturn('file');
        $this->downloadableLinkModel->expects($this->any())->method('getSortOrder')
            ->willReturn(0);
        $this->downloadableLinkModel->expects($this->any())->method('getLinkFile')
            ->willReturn('file/link.gif');
        $this->downloadableLinkModel->expects($this->any())->method('getStoreTitle')
            ->willReturn('Store Title');
        $this->escaper->expects($this->any())->method('escapeHtml')
            ->willReturn('Link Title');
        $this->fileHelper->expects($this->any())->method('getFilePath')
            ->willReturn('/file/path/link.gif');
        $this->fileHelper->expects($this->any())->method('ensureFileInFilesystem')
            ->willReturn(true);
        $this->fileHelper->expects($this->any())->method('getFileSize')
            ->willReturn('1.1');
        $this->urlBuilder->expects($this->any())->method('getUrl')
            ->willReturn('final_url');
        $linkData = $this->block->getLinkData();
        foreach ($linkData as $link) {
            $fileSave = $link->getFileSave(0);
            $sampleFileSave = $link->getSampleFileSave(0);
            $this->assertEquals($expectingFileData['file'], $fileSave);
            $this->assertEquals($expectingFileData['sample_file'], $sampleFileSave);
        }
    }
}
