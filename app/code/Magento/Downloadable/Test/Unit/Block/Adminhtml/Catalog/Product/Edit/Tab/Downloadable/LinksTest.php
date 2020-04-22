<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Downloadable\Test\Unit\Block\Adminhtml\Catalog\Product\Edit\Tab\Downloadable;

use Magento\Backend\Model\Url;
use Magento\Backend\Model\UrlFactory;
use Magento\Catalog\Model\Product;
use Magento\Downloadable\Block\Adminhtml\Catalog\Product\Edit\Tab\Downloadable\Links;
use Magento\Downloadable\Helper\File;
use Magento\Downloadable\Model\Link;
use Magento\Downloadable\Model\Product\Type;
use Magento\Eav\Model\Entity\AttributeFactory;
use Magento\Framework\DataObject;
use Magento\Framework\Escaper;
use Magento\Framework\Registry;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * @deprecated Class replaced by other element
 * @see \Magento\Downloadable\Ui\DataProvider\Product\Form\Modifier\Links
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class LinksTest extends TestCase
{
    /**
     * @var Links
     */
    protected $block;

    /**
     * @var Product
     */
    protected $productModel;

    /**
     * @var Type
     */
    protected $downloadableProductModel;

    /**
     * @var Link
     */
    protected $downloadableLinkModel;

    /**
     * @var Escaper
     */
    protected $escaper;

    /**
     * @var File
     */
    protected $fileHelper;

    /**
     * @var Registry
     */
    protected $coreRegistry;

    /**
     * @var Url
     */
    protected $urlBuilder;

    protected function setUp(): void
    {
        $objectManagerHelper = new ObjectManager($this);
        $this->urlBuilder = $this->createPartialMock(Url::class, ['getUrl']);
        $attributeFactory = $this->createMock(AttributeFactory::class);
        $urlFactory = $this->createMock(UrlFactory::class);
        $this->fileHelper = $this->createPartialMock(File::class, [
            'getFilePath',
            'ensureFileInFilesystem',
            'getFileSize'
        ]);
        $this->productModel = $this->createPartialMock(Product::class, [
            '__wakeup',
            'getTypeId',
            'getTypeInstance',
            'getStoreId'
        ]);
        $this->downloadableProductModel = $this->getMockBuilder(Type::class)
            ->addMethods(['__wakeup'])
            ->onlyMethods(['getLinks'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->downloadableLinkModel = $this->getMockBuilder(Link::class)
            ->addMethods(['getStoreTitle'])
            ->onlyMethods([
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
                'getLinkFile'
            ])
            ->disableOriginalConstructor()
            ->getMock();

        $this->coreRegistry = $this->getMockBuilder(Registry::class)
            ->addMethods(['__wakeup'])
            ->onlyMethods(['registry'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->escaper = $this->createPartialMock(Escaper::class, ['escapeHtml']);

        $this->block = $objectManagerHelper->getObject(
            Links::class,
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
        $this->assertInstanceOf(DataObject::class, $this->block->getConfig());
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
