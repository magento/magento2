<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Block\Adminhtml\Catalog\Product\Edit\Tab\Downloadable;

class LinksTest extends \PHPUnit_Framework_TestCase
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

    protected function setUp()
    {
        $objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->urlBuilder = $this->getMock('Magento\Backend\Model\Url', ['getUrl'], [], '', false);
        $attributeFactory = $this->getMock('Magento\Eav\Model\Entity\AttributeFactory', [], [], '', false);
        $urlFactory = $this->getMock('Magento\Backend\Model\UrlFactory', [], [], '', false);
        $this->fileHelper = $this->getMock(
            '\Magento\Downloadable\Helper\File',
            [
                'getFilePath',
                'ensureFileInFilesystem',
                'getFileSize'
            ],
            [],
            '',
            false
        );
        $this->productModel = $this->getMock(
            'Magento\Catalog\Model\Product',
            [
                '__wakeup',
                'getTypeId',
                'getTypeInstance',
                'getStoreId'
            ],
            [],
            '',
            false
        );
        $this->downloadableProductModel = $this->getMock(
            '\Magento\Downloadable\Model\Product\Type',
            [
                '__wakeup',
                'getLinks'
            ],
            [],
            '',
            false
        );
        $this->downloadableLinkModel = $this->getMock(
            '\Magento\Downloadable\Model\Link',
            [
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
            ],
            [],
            '',
            false
        );

        $this->coreRegistry = $this->getMock(
            '\Magento\Framework\Registry',
            [
                '__wakeup',
                'registry'
            ],
            [],
            '',
            false
        );

        $this->escaper = $this->getMock('\Magento\Framework\Escaper', ['escapeHtml'], [], '', false);

        $this->block = $objectManagerHelper->getObject(
            'Magento\Downloadable\Block\Adminhtml\Catalog\Product\Edit\Tab\Downloadable\Links',
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
     * Test that getConfig method retrieve \Magento\Framework\Object object
     */
    public function testGetConfig()
    {
        $this->assertInstanceOf('Magento\Framework\Object', $this->block->getConfig());
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
            ->will($this->returnValue('downloadable'));
        $this->productModel->expects($this->any())->method('getTypeInstance')
            ->will($this->returnValue($this->downloadableProductModel));
        $this->productModel->expects($this->any())->method('getStoreId')
            ->will($this->returnValue(0));
        $this->downloadableProductModel->expects($this->any())->method('getLinks')
            ->will($this->returnValue([$this->downloadableLinkModel]));
        $this->coreRegistry->expects($this->any())->method('registry')
            ->will($this->returnValue($this->productModel));
        $this->downloadableLinkModel->expects($this->any())->method('getId')
            ->will($this->returnValue(1));
        $this->downloadableLinkModel->expects($this->any())->method('getTitle')
            ->will($this->returnValue('Link Title'));
        $this->downloadableLinkModel->expects($this->any())->method('getPrice')
            ->will($this->returnValue('10'));
        $this->downloadableLinkModel->expects($this->any())->method('getNumberOfDownloads')
            ->will($this->returnValue('6'));
        $this->downloadableLinkModel->expects($this->any())->method('getLinkUrl')
            ->will($this->returnValue(null));
        $this->downloadableLinkModel->expects($this->any())->method('getLinkType')
            ->will($this->returnValue('file'));
        $this->downloadableLinkModel->expects($this->any())->method('getSampleFile')
            ->will($this->returnValue('file/sample.gif'));
        $this->downloadableLinkModel->expects($this->any())->method('getSampleType')
            ->will($this->returnValue('file'));
        $this->downloadableLinkModel->expects($this->any())->method('getSortOrder')
            ->will($this->returnValue(0));
        $this->downloadableLinkModel->expects($this->any())->method('getLinkFile')
            ->will($this->returnValue('file/link.gif'));
        $this->downloadableLinkModel->expects($this->any())->method('getStoreTitle')
            ->will($this->returnValue('Store Title'));
        $this->escaper->expects($this->any())->method('escapeHtml')
            ->will($this->returnValue('Link Title'));
        $this->fileHelper->expects($this->any())->method('getFilePath')
            ->will($this->returnValue('/file/path/link.gif'));
        $this->fileHelper->expects($this->any())->method('ensureFileInFilesystem')
            ->will($this->returnValue(true));
        $this->fileHelper->expects($this->any())->method('getFileSize')
            ->will($this->returnValue('1.1'));
        $this->urlBuilder->expects($this->any())->method('getUrl')
            ->will($this->returnValue('final_url'));
        $linkData = $this->block->getLinkData();
        foreach ($linkData as $link) {
            $fileSave = $link->getFileSave(0);
            $sampleFileSave = $link->getSampleFileSave(0);
            $this->assertEquals($expectingFileData['file'], $fileSave);
            $this->assertEquals($expectingFileData['sample_file'], $sampleFileSave);
        }
    }
}
