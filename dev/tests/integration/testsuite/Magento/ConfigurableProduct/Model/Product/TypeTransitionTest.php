<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Model\Product;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\TypeTransitionManager;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Integration test for configurable product type preservation when variants are removed
 */
class TypeTransitionTest extends TestCase
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var TypeTransitionManager
     */
    private $typeTransitionManager;

    protected function setUp(): void
    {
        $this->productRepository = Bootstrap::getObjectManager()->get(ProductRepositoryInterface::class);
        $this->typeTransitionManager = Bootstrap::getObjectManager()->get(TypeTransitionManager::class);
    }

    /**
     * @magentoDataFixture Magento/ConfigurableProduct/_files/product_configurable.php
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @return void
     */
    public function testConfigurableProductTypePreservedByTypeTransitionManager(): void
    {
        $product = $this->productRepository->get('configurable');
        $this->assertEquals(Configurable::TYPE_CODE, $product->getTypeId());

        $this->typeTransitionManager->processProduct($product);

        $this->assertEquals(
            Configurable::TYPE_CODE,
            $product->getTypeId(),
            'TypeTransitionManager must not convert configurable products to simple'
        );
    }
}
