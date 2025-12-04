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
use Magento\Downloadable\Block\Adminhtml\Catalog\Product\Edit\Tab\Downloadable\Samples;
use Magento\Downloadable\Helper\File;
use Magento\Downloadable\Model\Product\Type;
use Magento\Downloadable\Model\Sample;
use Magento\Downloadable\Test\Unit\Helper\SampleTestHelper;
use Magento\Downloadable\Test\Unit\Helper\TypeTestHelper;
use Magento\Framework\DataObject;
use Magento\Framework\Escaper;
use Magento\Framework\Registry;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

/**
 * @deprecated Class replaced by other element
 * @see \Magento\Downloadable\Ui\DataProvider\Product\Form\Modifier\Samples
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
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

    /**
     * @throws Exception
     */
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
        $this->downloadableProductModel = new TypeTestHelper();
        $this->downloadableSampleModel = new SampleTestHelper();
        $this->coreRegistry = $this->createPartialMock(Registry::class, ['registry']);
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

        $this->productModel->method('getTypeId')->willReturn('downloadable');
        $this->productModel->method('getTypeInstance')->willReturn($this->downloadableProductModel);
        $this->productModel->method('getStoreId')->willReturn(0);
        // Configure the sample model for this test
        $this->downloadableSampleModel->setId(1)
            ->setTitle('Sample Title')
            ->setSampleUrl(null)
            ->setSampleFile('file/sample.gif')
            ->setSampleType('file')
            ->setSortOrder(0);

        $this->downloadableProductModel->setSamples([$this->downloadableSampleModel]);
        $this->coreRegistry->method('registry')->willReturn($this->productModel);
        $this->escaper->method('escapeHtml')->willReturn('Sample Title');
        $this->fileHelper->method('getFilePath')->willReturn('/file/path/sample.gif');
        $this->fileHelper->method('ensureFileInFilesystem')->willReturn(true);
        $this->fileHelper->method('getFileSize')->willReturn('1.1');
        $this->urlBuilder->method('getUrl')->willReturn('final_url');
        $sampleData = $this->block->getSampleData();
        foreach ($sampleData as $sample) {
            $fileSave = $sample->getFileSave(0);
            $this->assertEquals($expectingFileData['sample_file'], $fileSave);
        }
    }
}
