<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Block\Adminhtml\Product\Composite\Fieldset;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Helper\Product as HelperProduct;
use Magento\Framework\DataObject;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\LayoutInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Test Qty block in composite product configuration layout
 *
 * @see \Magento\Catalog\Block\Adminhtml\Product\Composite\Fieldset\Qty
 * @magentoAppArea adminhtml
 */
class QtyTest extends TestCase
{
    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var Qty */
    private $block;

    /** @var ProductRepositoryInterface */
    private $productRepository;

    /** @var Registry */
    private $registry;

    /** @var HelperProduct */
    private $helperProduct;

    /** @var DataObjectFactory */
    private $dataObjectFactory;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->block = $this->objectManager->get(LayoutInterface::class)->createBlock(Qty::class);
        $this->registry = $this->objectManager->get(Registry::class);
        $this->productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $this->productRepository->cleanCache();
        $this->helperProduct = $this->objectManager->get(HelperProduct::class);
        $this->dataObjectFactory = $this->objectManager->get(DataObjectFactory::class);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        $this->registry->unregister('current_product');
        $this->registry->unregister('product');

        parent::tearDown();
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple_duplicated.php
     * @return void
     */
    public function testGetProduct(): void
    {
        $product = $this->productRepository->get('simple-1');
        $this->registerProduct($product);
        $this->assertEquals(
            $product->getId(),
            $this->block->getProduct()->getId(),
            'The expected product is missing in the Qty block!'
        );
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple_duplicated.php
     * @dataProvider getQtyValueProvider
     * @param bool $isQty
     * @param int $qty
     * @return void
     */
    public function testGetQtyValue(bool $isQty = false, int $qty = 1): void
    {
        $product = $this->productRepository->get('simple-1');
        if ($isQty) {
            /** @var DataObject $request */
            $buyRequest = $this->dataObjectFactory->create();
            $buyRequest->setData(['qty' => $qty]);
            $this->helperProduct->prepareProductOptions($product, $buyRequest);
        }
        $this->registerProduct($product);
        $this->assertEquals($qty, $this->block->getQtyValue(), 'Expected block qty value is incorrect!');
    }

    /**
     * Provides test data to verify block qty value.
     *
     * @return array
     */
    public static function getQtyValueProvider(): array
    {
        return [
            'with_qty' => [
                'isQty' => true,
                'qty' => 5,
            ],
            'without_qty' => [],
        ];
    }

    /**
     * Register the product
     *
     * @param ProductInterface $product
     * @return void
     */
    private function registerProduct(ProductInterface $product): void
    {
        $this->registry->unregister('current_product');
        $this->registry->unregister('product');
        $this->registry->register('current_product', $product);
        $this->registry->register('product', $product);
    }
}
