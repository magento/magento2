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
use Magento\Downloadable\Block\Adminhtml\Catalog\Product\Edit\Tab\Downloadable\Samples;
use Magento\Downloadable\Helper\File;
use Magento\Downloadable\Model\Product\Type;
use Magento\Downloadable\Model\Sample;
use Magento\Framework\DataObject;
use Magento\Framework\Escaper;
use Magento\Framework\Registry;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * @deprecated Class replaced by other element
 * @see \Magento\Downloadable\Ui\DataProvider\Product\Form\Modifier\Samples
 */
class SamplesTest extends TestCase
{
    /**
     * @var Samples
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
     * @var Sample
     */
    protected $downloadableSampleModel;

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
            ->onlyMethods(['getSamples'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->downloadableSampleModel = $this->createPartialMock(Sample::class, [
            '__wakeup',
            'getId',
            'getTitle',
            'getSampleFile',
            'getSampleType',
            'getSortOrder',
            'getSampleUrl'
        ]);
        $this->coreRegistry = $this->getMockBuilder(Registry::class)
            ->addMethods(['__wakeup'])
            ->onlyMethods(['registry'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->escaper = $this->createPartialMock(Escaper::class, ['escapeHtml']);
        $this->block = $objectManagerHelper->getObject(
            Samples::class,
            [
                'urlBuilder' => $this->urlBuilder,
                'urlFactory' => $urlFactory,
                'coreRegistry' => $this->coreRegistry,
                'escaper' => $this->escaper,
                'downloadableFile' => $this->fileHelper]
        );
    }

    /**
     * Test that getConfig method retrieve \Magento\Framework\DataObject object
     */
    public function testGetConfig()
    {
        $this->assertInstanceOf(DataObject::class, $this->block->getConfig());
    }

    public function testGetSampleData()
    {
        $expectingFileData = [
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
        $this->downloadableProductModel->expects($this->any())->method('getSamples')
            ->willReturn([$this->downloadableSampleModel]);
        $this->coreRegistry->expects($this->any())->method('registry')
            ->willReturn($this->productModel);
        $this->downloadableSampleModel->expects($this->any())->method('getId')
            ->willReturn(1);
        $this->downloadableSampleModel->expects($this->any())->method('getTitle')
            ->willReturn('Sample Title');
        $this->downloadableSampleModel->expects($this->any())->method('getSampleUrl')
            ->willReturn(null);
        $this->downloadableSampleModel->expects($this->any())->method('getSampleFile')
            ->willReturn('file/sample.gif');
        $this->downloadableSampleModel->expects($this->any())->method('getSampleType')
            ->willReturn('file');
        $this->downloadableSampleModel->expects($this->any())->method('getSortOrder')
            ->willReturn(0);
        $this->escaper->expects($this->any())->method('escapeHtml')
            ->willReturn('Sample Title');
        $this->fileHelper->expects($this->any())->method('getFilePath')
            ->willReturn('/file/path/sample.gif');
        $this->fileHelper->expects($this->any())->method('ensureFileInFilesystem')
            ->willReturn(true);
        $this->fileHelper->expects($this->any())->method('getFileSize')
            ->willReturn('1.1');
        $this->urlBuilder->expects($this->any())->method('getUrl')
            ->willReturn('final_url');
        $sampleData = $this->block->getSampleData();
        foreach ($sampleData as $sample) {
            $fileSave = $sample->getFileSave(0);
            $this->assertEquals($expectingFileData['sample_file'], $fileSave);
        }
    }
}
