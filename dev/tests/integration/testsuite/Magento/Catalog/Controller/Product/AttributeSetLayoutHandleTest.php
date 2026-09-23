<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Controller\Product;

use Magento\Catalog\Helper\Product\View;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Test\Fixture\AttributeSet as AttributeSetFixture;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Framework\App\Cache\Type\Layout as LayoutCache;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\EntitySpecificHandlesList;
use Magento\Framework\View\Layout\GeneratorPool;
use Magento\Framework\View\Layout\ProcessorInterface;
use Magento\Framework\View\Layout\ReaderPool;
use Magento\Framework\View\Layout\ScheduledStructure;
use Magento\Framework\View\LayoutInterface;
use Magento\Framework\View\Model\Layout\Merge;
use Magento\Framework\View\Result\PageFactory;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Integration test to verify layout handles are generated based on product attribute sets.
 *
 * @magentoAppArea frontend
 * @magentoAppIsolation enabled
 * @magentoDbIsolation disabled
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AttributeSetLayoutHandleTest extends TestCase
{
    /**
     * @var DataFixtureStorage
     */
    private DataFixtureStorage $fixtures;

    /**
     * @var ObjectManagerInterface
     */
    private ObjectManagerInterface $objectManager;

    /**
     * Handle prefix for full format attribute set handles
     */
    private const HANDLE_PREFIX_ATTRIBUTE_SET_FULL = 'catalog_product_view_attribute_set_';

    /**
     * Handle prefix for shorthand format attribute set handles
     */
    private const HANDLE_PREFIX_ATTRIBUTE_SET_SHORT = '___attribute_set_';

    /**
     * Handle prefix for full format type handles
     */
    private const HANDLE_PREFIX_TYPE_FULL = 'catalog_product_view_type_';

    /**
     * Handle prefix for shorthand format type handles
     */
    private const HANDLE_PREFIX_TYPE_SHORT = '___type_';

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->fixtures = $this->objectManager->get(DataFixtureStorageManager::class)->getStorage();
    }

    /**
     * Get layout handles for a product
     *
     * @param Product $product
     * @return string[]
     */
    private function getProductLayoutHandles(Product $product): array
    {
        $this->performIsolation();

        $viewHelper = $this->objectManager->get(View::class);
        $resultPage = $this->objectManager->create(PageFactory::class)->create();

        $viewHelper->initProductLayout($resultPage, $product);

        return $resultPage->getLayout()->getUpdate()->getHandles();
    }

    /**
     * Perform isolation by removing shared instances and clearing cache
     *
     * @return void
     */
    private function performIsolation(): void
    {
        $this->objectManager->removeSharedInstance(LayoutInterface::class);
        $this->objectManager->removeSharedInstance(\Magento\Framework\View\Layout::class);
        $this->objectManager->removeSharedInstance(EntitySpecificHandlesList::class);
        $this->objectManager->removeSharedInstance(Merge::class);
        $this->objectManager->removeSharedInstance(ProcessorInterface::class);
        $this->objectManager->removeSharedInstance(ReaderPool::class);
        $this->objectManager->removeSharedInstance(GeneratorPool::class);
        $this->objectManager->removeSharedInstance(ScheduledStructure::class);
        $this->objectManager->removeSharedInstance(PageFactory::class);
        $this->objectManager->removeSharedInstance(View::class);

        /** @var LayoutCache $layoutCache */
        $layoutCache = $this->objectManager->get(LayoutCache::class);
        $layoutCache->clean();
    }

    /**
     * Get attribute set handle name for a product (full format)
     *
     * @param Product $product
     * @return string
     */
    private function getAttributeSetHandleName(Product $product): string
    {
        return self::HANDLE_PREFIX_ATTRIBUTE_SET_FULL . $product->getAttributeSetId();
    }

    /**
     * Get attribute set handle shorthand name for a product
     *
     * @param Product $product
     * @return string
     */
    private function getAttributeSetHandleShorthand(Product $product): string
    {
        return self::HANDLE_PREFIX_ATTRIBUTE_SET_SHORT . $product->getAttributeSetId();
    }

    /**
     * Get product type handle name (full format)
     *
     * @param Product $product
     * @return string
     */
    private function getProductTypeHandleName(Product $product): string
    {
        return self::HANDLE_PREFIX_TYPE_FULL . $product->getTypeId();
    }

    /**
     * Get product type handle shorthand name
     *
     * @param Product $product
     * @return string
     */
    private function getProductTypeHandleShorthand(Product $product): string
    {
        return self::HANDLE_PREFIX_TYPE_SHORT . $product->getTypeId();
    }

    /**
     * Assert product has its attribute set handle
     *
     * @param Product $product
     * @param string[] $handles
     * @return void
     */
    private function assertHasAttributeSetHandle(Product $product, array $handles): void
    {
        $expectedHandleFull = $this->getAttributeSetHandleName($product);
        $expectedHandleShort = $this->getAttributeSetHandleShorthand($product);

        $hasFullHandle = in_array($expectedHandleFull, $handles, true);
        $hasShortHandle = in_array($expectedHandleShort, $handles, true);

        $this->assertTrue(
            $hasFullHandle || $hasShortHandle,
            sprintf(
                "Product '%s' (ID: %d, AttributeSet: %d) should have handle '%s' or '%s'. "
                . "Available handles: %s",
                $product->getSku(),
                $product->getId(),
                $product->getAttributeSetId(),
                $expectedHandleFull,
                $expectedHandleShort,
                implode(', ', $handles)
            )
        );
    }

    /**
     * Assert product has its product type handle
     *
     * @param Product $product
     * @param string[] $handles
     * @return void
     */
    private function assertHasProductTypeHandle(Product $product, array $handles): void
    {
        $expectedHandleFull = $this->getProductTypeHandleName($product);
        $expectedHandleShort = $this->getProductTypeHandleShorthand($product);

        $hasFullHandle = in_array($expectedHandleFull, $handles, true);
        $hasShortHandle = in_array($expectedHandleShort, $handles, true);

        $this->assertTrue(
            $hasFullHandle || $hasShortHandle,
            sprintf(
                "Product '%s' (Type: %s) should have type handle '%s' or '%s'. "
                . "Available handles: %s",
                $product->getSku(),
                $product->getTypeId(),
                $expectedHandleFull,
                $expectedHandleShort,
                implode(', ', $handles)
            )
        );
    }

    /**
     * Test that product with unique attribute set gets its own layout handle
     *
     * @return void
     */
    #[
        DataFixture(AttributeSetFixture::class, ['attribute_set_name' => 'Custom Set A'], as: 'attribute_set_a'),
        DataFixture(
            ProductFixture::class,
            ['sku' => 'product-set-a-1', 'attribute_set_id' => '$attribute_set_a.attribute_set_id$'],
            as: 'product_a'
        )
    ]
    public function testProductWithUniqueAttributeSetGetsItsOwnHandle(): void
    {
        $product = $this->fixtures->get('product_a');
        $handles = $this->getProductLayoutHandles($product);

        $this->assertHasAttributeSetHandle($product, $handles);
        $this->assertHasProductTypeHandle($product, $handles);
        $this->assertContains('default', $handles, 'Product layout should include default handle');
    }

    /**
     * Test that two products with same attribute set get the same layout handle
     *
     * @return void
     */
    #[
        DataFixture(AttributeSetFixture::class, ['attribute_set_name' => 'Custom Set B'], as: 'attribute_set_b'),
        DataFixture(
            ProductFixture::class,
            ['sku' => 'product-set-b-1', 'attribute_set_id' => '$attribute_set_b.attribute_set_id$'],
            as: 'product_b1'
        ),
        DataFixture(
            ProductFixture::class,
            ['sku' => 'product-set-b-2', 'attribute_set_id' => '$attribute_set_b.attribute_set_id$'],
            as: 'product_b2'
        )
    ]
    public function testProductsWithSameAttributeSetGetSameHandle(): void
    {
        $product1 = $this->fixtures->get('product_b1');
        $product2 = $this->fixtures->get('product_b2');

        $this->assertEquals(
            $product1->getAttributeSetId(),
            $product2->getAttributeSetId(),
            'Both products should have the same attribute set'
        );

        $handles1 = $this->getProductLayoutHandles($product1);
        $handles2 = $this->getProductLayoutHandles($product2);

        $this->assertHasAttributeSetHandle($product1, $handles1);
        $this->assertHasAttributeSetHandle($product2, $handles2);

        $expectedHandleFull = $this->getAttributeSetHandleName($product1);
        $expectedHandleShort = $this->getAttributeSetHandleShorthand($product1);

        $product1HasHandle = in_array($expectedHandleFull, $handles1, true)
            || in_array($expectedHandleShort, $handles1, true);
        $product2HasHandle = in_array($expectedHandleFull, $handles2, true)
            || in_array($expectedHandleShort, $handles2, true);

        $this->assertTrue($product1HasHandle, 'Product 1 should have the attribute set handle');
        $this->assertTrue($product2HasHandle, 'Product 2 should have the SAME attribute set handle');

        $this->assertHasProductTypeHandle($product1, $handles1);
        $this->assertHasProductTypeHandle($product2, $handles2);
    }
}
