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
