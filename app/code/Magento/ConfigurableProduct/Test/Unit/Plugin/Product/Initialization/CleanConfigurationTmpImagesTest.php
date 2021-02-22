<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Test\Unit\Plugin\Product\Initialization;

use Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper as ProductInitializationHelper;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Media\Config as MediaConfig;
use Magento\ConfigurableProduct\Plugin\Product\Initialization\CleanConfigurationTmpImages;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\Write;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\MediaStorage\Helper\File\Storage\Database as FileStorage;

/**
 * Class CleanConfigurationTmpImagesTest
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPCS.Magento2.Files.LineLength.MaxExceeded)
 * @package Magento\ConfigurableProduct\Test\Unit\Controller\Adminhtml\Product\Initialization\Helper\Plugin
 */
class CleanConfigurationTmpImagesTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var CleanConfigurationTmpImages
     */
    private $cleanConfigurationTmpImages;

    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var RequestInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $requestMock;

    /**
     * @var FileStorage|\PHPUnit\Framework\MockObject\MockObject
     */
    private $fileStorageDb;

    /**
     * @var MediaConfig|\PHPUnit\Framework\MockObject\MockObject
     */
    private $mediaConfig;

    /**
     * @var Filesystem|\PHPUnit\Framework\MockObject\MockObject
     */
    private $filesystem;

    /**
     * @var Write|\PHPUnit\Framework\MockObject\MockObject
     */
    private $writeFolder;

    /**
     * @var Json|\PHPUnit\Framework\MockObject\MockObject
     */
    private $seralizer;

    /**
     * @var ProductInitializationHelper|\PHPUnit\Framework\MockObject\MockObject
     */
    private $subjectMock;

    protected function setUp(): void
    {
        $this->requestMock = $this->getMockBuilder(RequestInterface::class)
            ->getMockForAbstractClass();
        $this->fileStorageDb = $this->getMockBuilder(FileStorage::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->mediaConfig = $this->getMockBuilder(MediaConfig::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->filesystem = $this->getMockBuilder(Filesystem::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->writeFolder = $this->getMockBuilder(Write::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->seralizer = $this->getMockBuilder(Json::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->subjectMock = $this->getMockBuilder(ProductInitializationHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->filesystem->expects($this->once())
            ->method('getDirectoryWrite')
            ->willReturn($this->writeFolder);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->cleanConfigurationTmpImages = $this->objectManagerHelper->getObject(
            CleanConfigurationTmpImages::class,
            [
                'request' => $this->requestMock,
                'fileStorageDb' => $this->fileStorageDb,
                'mediaConfig' => $this->mediaConfig,
                'filesystem' => $this->filesystem,
                'seralizer' => $this->seralizer
            ]
        );
    }

    /**
     * Prepare configurable matrix
     *
     * @return array
     */
    private function getConfigurableMatrix()
    {
        return [
            [
                'newProduct' => true,
                'id' => 'product1'
            ],
            [
                'newProduct' => false,
                'id' => 'product2',
                'status' => 'simple2_status',
                'sku' => 'simple2_sku',
                'name' => 'simple2_name',
                'price' => '3.33',
                'configurable_attribute' => 'simple2_configurable_attribute',
                'weight' => '5.55',
                'media_gallery' => [
                    'images' => [
                        ['file' => 'test']
                    ],
                ],
                'swatch_image' => 'simple2_swatch_image',
                'small_image' => 'simple2_small_image',
                'thumbnail' => 'simple2_thumbnail',
                'image' => 'simple2_image',
                'was_changed' => true,
            ],
            [
                'newProduct' => false,
                'id' => 'product3',
                'qty' => '3',
                'was_changed' => true,
            ],
            [
                'newProduct' => false,
                'id' => 'product4',
                'status' => 'simple4_status',
                'sku' => 'simple2_sku',
                'name' => 'simple2_name',
                'price' => '3.33',
                'weight' => '5.55',
            ],
        ];
    }

    public function testAfterInitialize()
    {
        $productMock = $this->getProductMock();
        $configurableMatrix = $this->getConfigurableMatrix();

        $this->requestMock->expects(static::any())
            ->method('getParam')
            ->willReturnMap(
                [
                    ['store', 0, 0],
                    ['configurable-matrix-serialized', "[]", json_encode($configurableMatrix)]
                ]
            );

        $this->assertSame(
            $productMock,
            $this->cleanConfigurationTmpImages->afterInitialize($this->subjectMock, $productMock)
        );
    }

    /**
     * Get product mock
     *
     * @param array $expectedData
     * @param bool $hasDataChanges
     * @param bool $wasChanged
     * @return Product|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function getProductMock(array $expectedData = null, $hasDataChanges = false, $wasChanged = false)
    {
        $productMock = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();

        if ($wasChanged !== false) {
            if ($expectedData !== null) {
                $productMock->expects(static::once())
                    ->method('addData')
                    ->with($expectedData)
                    ->willReturnSelf();
            }

            $productMock->expects(static::any())
                ->method('hasDataChanges')
                ->willReturn($hasDataChanges);
            $productMock->expects($hasDataChanges ? static::once() : static::never())
                ->method('save')
                ->willReturnSelf();
        }
        return $productMock;
    }

    /**
     * Test for no exceptions if configurable matrix is empty string.
     */
    public function testAfterInitializeEmptyMatrix()
    {
        $productMock = $this->getProductMock();

        $this->requestMock->expects(static::any())
            ->method('getParam')
            ->willReturnMap(
                [
                    ['store', 0, 0],
                    ['configurable-matrix-serialized', null, ''],
                ]
            );

        $this->cleanConfigurationTmpImages->afterInitialize($this->subjectMock, $productMock);

        $this->assertEmpty($productMock->getData());
    }
}
