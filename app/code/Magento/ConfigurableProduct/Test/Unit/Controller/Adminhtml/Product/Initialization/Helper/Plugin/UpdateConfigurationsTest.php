<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Test\Unit\Controller\Adminhtml\Product\Initialization\Helper\Plugin;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper as ProductInitializationHelper;
use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Controller\Adminhtml\Product\Initialization\Helper\Plugin\UpdateConfigurations;
use Magento\ConfigurableProduct\Model\Product\VariationHandler;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class UpdateConfigurationsTest extends TestCase
{
    /**
     * @var UpdateConfigurations
     */
    private $updateConfigurations;

    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var RequestInterface|MockObject
     */
    private $requestMock;

    /**
     * @var ProductRepositoryInterface|MockObject
     */
    private $productRepositoryMock;

    /**
     * @var VariationHandler|MockObject
     */
    private $variationHandlerMock;

    /**
     * @var ProductInitializationHelper|MockObject
     */
    private $subjectMock;

    protected function setUp(): void
    {
        $this->requestMock = $this->getMockBuilder(RequestInterface::class)
            ->getMockForAbstractClass();
        $this->productRepositoryMock = $this->getMockBuilder(ProductRepositoryInterface::class)
            ->getMockForAbstractClass();
        $this->variationHandlerMock = $this->getMockBuilder(VariationHandler::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->subjectMock = $this->getMockBuilder(ProductInitializationHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->updateConfigurations = $this->objectManagerHelper->getObject(
            UpdateConfigurations::class,
            [
                'request' => $this->requestMock,
                'productRepository' => $this->productRepositoryMock,
                'variationHandler' => $this->variationHandlerMock
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
                'media_gallery' => 'simple2_media_gallery',
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
            [
                'newProduct' => false,
                'id' => 'product5',
                'status' => 'simple5_status',
                'sku' => 'simple5_sku',
                'name' => 'simple5_name',
                'price' => '3.33',
                'configurable_attribute' => 'simple5_configurable_attribute',
                'weight' => '',
                'media_gallery' => 'simple5_media_gallery',
                'swatch_image' => 'simple5_swatch_image',
                'small_image' => 'simple5_small_image',
                'thumbnail' => 'simple5_thumbnail',
                'image' => 'simple5_image',
                'was_changed' => true,
            ],
        ];
    }

    public function testAfterInitialize()
    {
        $productMock = $this->getProductMock();
        $configurableMatrix = $this->getConfigurableMatrix();
        $configurations = [
            'product2' => [
                'status' => 'simple2_status',
                'sku' => 'simple2_sku',
                'name' => 'simple2_name',
                'price' => '3.33',
                'configurable_attribute' => 'simple2_configurable_attribute',
                'weight' => '5.55',
                'media_gallery' => 'simple2_media_gallery',
                'swatch_image' => 'simple2_swatch_image',
                'small_image' => 'simple2_small_image',
                'thumbnail' => 'simple2_thumbnail',
                'image' => 'simple2_image',
                'product_has_weight' => 1,
                'type_id' => 'simple'
            ],
            'product3' => [
                'quantity_and_stock_status' => ['qty' => '3']
            ],
            'product5' => [
                'status' => 'simple5_status',
                'sku' => 'simple5_sku',
                'name' => 'simple5_name',
                'price' => '3.33',
                'configurable_attribute' => 'simple5_configurable_attribute',
                'weight' => '',
                'media_gallery' => 'simple5_media_gallery',
                'swatch_image' => 'simple5_swatch_image',
                'small_image' => 'simple5_small_image',
                'thumbnail' => 'simple5_thumbnail',
                'image' => 'simple5_image',
            ],
        ];
        /** @var Product[]|MockObject[] $productMocks */
        $productMocks = [
            'product2' => $this->getProductMock($configurations['product2'], true, true),
            'product3' => $this->getProductMock($configurations['product3'], false, true),
            'product5' => $this->getProductMock($configurations['product5'], false, true),
        ];

        $this->requestMock->expects(static::any())
            ->method('getParam')
            ->willReturnMap(
                [
                    ['store', 0, 0],
                    ['configurable-matrix-serialized', "[]", json_encode($configurableMatrix)]
                ]
            );
        $this->variationHandlerMock->expects(static::once())
            ->method('duplicateImagesForVariations')
            ->with($configurations)
            ->willReturn($configurations);
        $this->productRepositoryMock->expects(static::any())
            ->method('getById')
            ->willReturnMap(
                [
                    ['product2', false, 0, false, $productMocks['product2']],
                    ['product3', false, 0, false, $productMocks['product3']],
                    ['product5', false, 0, false, $productMocks['product5']],
                ]
            );
        $this->variationHandlerMock->expects(static::any())
            ->method('processMediaGallery')
            ->willReturnMap(
                [
                    [$productMocks['product2'], $configurations['product2'], $configurations['product2']],
                    [$productMocks['product3'], $configurations['product3'], $configurations['product3']],
                    [$productMocks['product5'], $configurations['product5'], $configurations['product5']]
                ]
            );

        $this->assertSame($productMock, $this->updateConfigurations->afterInitialize($this->subjectMock, $productMock));
    }

    /**
     * Get product mock
     *
     * @param array $expectedData
     * @param bool $hasDataChanges
     * @param bool $wasChanged
     * @return Product|MockObject
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

        $this->variationHandlerMock->expects(static::once())
            ->method('duplicateImagesForVariations')
            ->with([])
            ->willReturn([]);

        $this->updateConfigurations->afterInitialize($this->subjectMock, $productMock);

        $this->assertEmpty($productMock->getData());
    }
}
