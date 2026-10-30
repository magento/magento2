<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
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
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

/**
 * @deprecated Class replaced by other element
 * @see \Magento\Downloadable\Ui\DataProvider\Product\Form\Modifier\Links
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class LinksTest extends TestCase
{
    use MockCreationTrait;

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
        $objectManagerHelper->prepareObjectManager();
        $this->setupMocks();
        $this->setupDownloadableProductModel();
        $this->setupLinksBlock($objectManagerHelper);
    }

    /**
     * Setup basic mocks for testing
     *
     * @return void
     */
    private function setupMocks(): void
    {
        $this->urlBuilder = $this->createPartialMock(Url::class, ['getUrl']);
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
    }

    /**
     * Setup downloadable product model mock
     *
     * @return void
     * @throws Exception
     */
    private function setupDownloadableProductModel(): void
    {
        $this->downloadableProductModel = $this->createPartialMockWithReflection(
            Type::class,
            ['getLinks']
        );
        
        $this->downloadableLinkModel = $this->createPartialMockWithReflection(
            Link::class,
            ['getId', 'getTitle', 'getPrice', 'getNumberOfDownloads', 'getLinkUrl', 'getLinkType',
             'getSampleFile', 'getSampleType', 'getSortOrder', 'getLinkFile', 'getStoreTitle']
        );

        $this->coreRegistry = $this->createPartialMock(Registry::class, ['registry']);

        $this->escaper = $this->createPartialMock(Escaper::class, ['escapeHtml']);
    }

    /**
     * Setup Links block with dependencies
     *
     * @param ObjectManager $objectManagerHelper
     * @return void
     */
    private function setupLinksBlock(ObjectManager $objectManagerHelper): void
    {
        $attributeFactory = $this->createMock(AttributeFactory::class);
        $urlFactory = $this->createMock(UrlFactory::class);

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

        $this->productModel->method('getTypeId')->willReturn('downloadable');
        $this->productModel->method('getTypeInstance')->willReturn($this->downloadableProductModel);
        $this->productModel->method('getStoreId')->willReturn(0);
        
        // Configure the link model for this test with getter returns instead of setters
        $this->downloadableLinkModel->method('getId')->willReturn(1);
        $this->downloadableLinkModel->method('getTitle')->willReturn('Link Title');
        $this->downloadableLinkModel->method('getPrice')->willReturn('10');
        $this->downloadableLinkModel->method('getNumberOfDownloads')->willReturn('6');
        $this->downloadableLinkModel->method('getLinkUrl')->willReturn(null);
        $this->downloadableLinkModel->method('getLinkType')->willReturn('file');
        $this->downloadableLinkModel->method('getSampleFile')->willReturn('file/sample.gif');
        $this->downloadableLinkModel->method('getSampleType')->willReturn('file');
        $this->downloadableLinkModel->method('getSortOrder')->willReturn(0);
        $this->downloadableLinkModel->method('getLinkFile')->willReturn('file/link.gif');
        $this->downloadableLinkModel->method('getStoreTitle')->willReturn('Store Title');

        $this->downloadableProductModel->method('getLinks')->willReturn([$this->downloadableLinkModel]);
        $this->coreRegistry->method('registry')->willReturn($this->productModel);
        $this->escaper->method('escapeHtml')->willReturn('Link Title');
        $this->fileHelper->method('getFilePath')->willReturn('/file/path/link.gif');
        $this->fileHelper->method('ensureFileInFilesystem')->willReturn(true);
        $this->fileHelper->method('getFileSize')->willReturn('1.1');
        $this->urlBuilder->method('getUrl')->willReturn('final_url');
        $linkData = $this->block->getLinkData();
        foreach ($linkData as $link) {
            $fileSave = $link->getFileSave(0);
            $sampleFileSave = $link->getSampleFileSave(0);
            $this->assertEquals($expectingFileData['file'], $fileSave);
            $this->assertEquals($expectingFileData['sample_file'], $sampleFileSave);
        }
    }
}
