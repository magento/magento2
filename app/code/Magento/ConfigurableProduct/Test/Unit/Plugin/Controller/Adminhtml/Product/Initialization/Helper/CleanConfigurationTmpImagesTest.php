<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Test\Unit\Plugin\Controller\Adminhtml\Product\Initialization\Helper;

use Magento\ConfigurableProduct\Plugin\Controller\Adminhtml\Product\Initialization\Helper\CleanConfigurationTmpImages;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\App\RequestInterface;
use Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper as ProductInitializationHelper;
use Magento\Catalog\Model\Product;
use Magento\Framework\Filesystem\Directory\Write;
use Magento\Catalog\Model\Product\Media\Config;
use Magento\MediaStorage\Helper\File\Storage\Database;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class for testing cleaning configuration tmp images
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CleanConfigurationTmpImagesTest extends TestCase
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
     * @var RequestInterface|MockObject
     */
    private $requestMock;

    /**
     * @var Config|MockObject
     */
    protected $mediaConfig;

    /**
     * @var Write|MockObject
     */
    private $mediaDirectory;

    /**
     * @var ProductInitializationHelper|MockObject
     */
    private $subjectMock;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $fileStorageDb = $this->createMock(Database::class);
        $this->mediaConfig = $this->createMock(Config::class);
        $this->mediaDirectory = $this->createMock(Write::class);
        $this->requestMock = $this->getMockBuilder(RequestInterface::class)
            ->getMockForAbstractClass();
        $this->subjectMock = $this->getMockBuilder(ProductInitializationHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->cleanConfigurationTmpImages = $this->objectManagerHelper->getObject(
            CleanConfigurationTmpImages::class,
            [
                'request' => $this->requestMock,
                'fileStorageDb' => $fileStorageDb,
                'mediaConfig' => $this->mediaConfig,
                'mediaDirectory' => $this->mediaDirectory
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
                'newProduct' => false,
                'id' => 'product2',
                'sku' => 'simple2_sku',
                'name' => 'simple2_name',
                'price' => '3.33',
                'configurable_attribute' => 'simple2_configurable_attribute',
                'was_changed' => true,
                'media_gallery' => [
                    'images' => [
                        [
                            'file' => 'a/b/test_image.png.tmp',
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Test after initialize
     *
     * @return void
     */
    public function testAfterInitialize()
    {
        $configurableMatrix = $this->getConfigurableMatrix();
        $this->requestMock->method('getParam')
            ->willReturnMap(
                [
                    ['store', 0, 0],
                    ['configurable-matrix-serialized', "[]", json_encode($configurableMatrix)]
                ]
            );

        /** @var Product|\PHPUnit_Framework_MockObject_MockObject $productMock */
        $productMock = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->mediaConfig->expects($this->once())->method('getTmpMediaPath');
        $this->mediaDirectory->expects($this->once())->method('delete');

        $this->assertSame(
            $productMock,
            $this->cleanConfigurationTmpImages->afterInitialize($this->subjectMock, $productMock)
        );
    }
}
