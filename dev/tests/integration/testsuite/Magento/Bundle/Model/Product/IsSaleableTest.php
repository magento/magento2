<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Bundle\Model\Product;

/**
 * Test class for \Magento\Bundle\Model\Product\Type (bundle product type)
 */
class IsSaleableTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->productRepository = $this->objectManager->get(\Magento\Catalog\Api\ProductRepositoryInterface::class);
    }

    /**
     * check bundle product is saleable if his status is enabled
     *
     * @magentoDataFixture Magento/Bundle/_files/issaleable_product.php
     * @magentoAppIsolation enabled
     * @covers \Magento\Bundle\Model\Product\Type::isSalable
     */
    public function testIsSaleableOnEnabledStatus()
    {

        $bundleProduct = $this->productRepository->get('bundle-product');
        $bundleProduct->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);

        $this->assertTrue(
            $bundleProduct->isSalable(),
            'Bundle product supposed to be saleable if his status is enabled'
        );
    }

    /**
     * check bundle product is NOT saleable if his status is disabled
     *
     * @magentoDataFixture Magento/Bundle/_files/issaleable_product.php
     * @magentoAppIsolation enabled
     * @covers \Magento\Bundle\Model\Product\Type::isSalable
     */
    public function testIsSaleableOnDisabledStatus()
    {
        $bundleProduct = $this->productRepository->get('bundle-product');
        $bundleProduct->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED);

        $this->assertFalse(
            $bundleProduct->isSalable(),
            'Bundle product supposed to be non saleable if his status is disabled'
        );
    }

    /**
     * check bundle product is saleable if his status is enabled
     * and it has internal data is_salable = true
     *
     * @magentoDataFixtureBeforeTransaction Magento/Bundle/_files/issaleable_product.php
     * @magentoAppIsolation enabled
     * @covers \Magento\Bundle\Model\Product\Type::isSalable
     */
    public function testIsSaleableOnEnabledStatusAndIsSalableIsTrue()
    {
        $bundleProduct = $this->productRepository->get('bundle-product');
        $bundleProduct->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);
        $bundleProduct->setData('is_salable', true);

        $this->assertTrue(
            $bundleProduct->isSalable(),
            'Bundle product supposed to be saleable if his status is enabled and it has data is_salable = true'
        );
    }

    /**
     * check bundle product is NOT saleable if
     * his status is enabled but his data is_salable = false
     *
     * @magentoDataFixture Magento/Bundle/_files/issaleable_product.php
     * @magentoAppIsolation enabled
     * @covers \Magento\Bundle\Model\Product\Type::isSalable
     */
    public function testIsSaleableOnEnabledStatusAndIsSalableIsFalse()
    {
        $bundleProduct = $this->productRepository->get('bundle-product');
        $bundleProduct->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);
        $bundleProduct->setData('is_salable', false);

        $this->assertFalse(
            $bundleProduct->isSalable(),
            'Bundle product supposed to be non saleable if his status is enabled but his data is_salable = false'
        );
    }

    /**
     * check bundle product is saleable if it has all_items_salable = true
     *
     * @magentoDataFixture Magento/Bundle/_files/issaleable_product.php
     * @magentoAppIsolation enabled
     * @covers \Magento\Bundle\Model\Product\Type::isSalable
     */
    public function testIsSaleableOnAllItemsSalableIsTrue()
    {
        $bundleProduct = $this->productRepository->get('bundle-product');
        $bundleProduct->setData('all_items_salable', true);

        $this->assertTrue(
            $bundleProduct->isSalable(),
            'Bundle product supposed to be saleable if it has data all_items_salable = true'
        );
    }

    /**
     * check bundle product is NOT saleable if it has all_items_salable = false
     *
     * @magentoDataFixture Magento/Bundle/_files/issaleable_product.php
     * @magentoAppIsolation enabled
     * @covers \Magento\Bundle\Model\Product\Type::isSalable
     */
    public function testIsSaleableOnAllItemsSalableIsFalse()
    {
        $bundleProduct = $this->productRepository->get('bundle-product');
        $bundleProduct->setData('all_items_salable', false);

        $this->assertFalse(
            $bundleProduct->isSalable(),
            'Bundle product supposed to be non saleable if it has data all_items_salable = false'
        );
    }

    /**
     * check bundle product is NOT saleable if it has no options
     *
     * @magentoDataFixtureBeforeTransaction Magento/Bundle/_files/issaleable_product.php
     * @magentoAppIsolation enabled
     * @covers \Magento\Bundle\Model\Product\Type::isSalable
     */
    public function testIsSaleableOnBundleWithoutOptions()
    {
        $optionRepository = $this->objectManager->create(\Magento\Bundle\Api\ProductOptionRepositoryInterface::class);

        $bundleProduct = $this->productRepository->get('bundle-product');

        // TODO: make cleaner option deletion after fix MAGETWO-59465
        $ea = $bundleProduct->getExtensionAttributes();
        foreach ($ea->getBundleProductOptions() as $option) {
            $optionRepository->delete($option);
        }
        $ea->setBundleProductOptions([]);
        $bundleProduct->setExtensionAttributes($ea);
        $bundleProduct->setBundleOptionsData([]);
        $this->productRepository->save($bundleProduct);

        $this->assertFalse(
            $bundleProduct->isSalable(),
            'Bundle product supposed to be non saleable if it has no options'
        );
    }

    /**
     * check bundle product is NOT saleable if it has no selections
     *
     * @magentoDataFixture Magento/Bundle/_files/issaleable_product.php
     * @magentoAppIsolation enabled
     * @covers \Magento\Bundle\Model\Product\Type::isSalable
     */
    public function testIsSaleableOnBundleWithoutSelections()
    {
        $bundleProductSku = 'bundle-product';

        $bundleProduct = $this->productRepository->get($bundleProductSku, true, null, true);
        $bundleType = $bundleProduct->getTypeInstance();
        /** @var  \Magento\Bundle\Model\LinkManagement $linkManager */
        $linkManager = $this->objectManager->create(\Magento\Bundle\Model\LinkManagement::class);

        /** @var \Magento\Bundle\Model\Product\Type $bundleType */
        $options = $bundleType->getOptionsCollection($bundleProduct);
        $selections = $bundleType->getSelectionsCollection($options->getAllIds(), $bundleProduct);

        foreach ($selections as $link) {
            /** @var \Magento\Bundle\Model\Selection $link */
            $linkManager->removeChild($bundleProductSku, $link->getOptionId(), $link->getSku());
        }

        $bundleProduct = $this->productRepository->get($bundleProductSku, true, null, true);

        $this->assertFalse(
            $bundleProduct->isSalable(),
            'Bundle product supposed to be non saleable if it has no selections'
        );
    }

    /**
     * check bundle product is NOT saleable if
     * all his selections are not saleable
     *
     * @magentoDataFixture Magento/Bundle/_files/issaleable_product.php
     * @magentoAppIsolation enabled
     * @covers \Magento\Bundle\Model\Product\Type::isSalable
     */
    public function testIsSaleableOnBundleWithoutSaleableSelections()
    {
        $productsSku = ['simple1', 'simple2', 'simple3', 'simple4', 'simple5'];
        foreach ($productsSku as $productSku) {
            $product = $this->productRepository->get($productSku);
            $product->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED);
            $this->productRepository->save($product);
        }

        $bundleProduct = $this->productRepository->get('bundle-product');

        $this->assertFalse(
            $bundleProduct->isSalable(),
            'Bundle product supposed to be non saleable if all his selections are not saleable'
        );
    }

    /**
     * check bundle product is NOT saleable if
     * it has at least one required option without saleable selections
     *
     * @magentoDataFixture Magento/Bundle/_files/issaleable_product.php
     * @magentoAppIsolation enabled
     * @covers \Magento\Bundle\Model\Product\Type::isSalable
     */
    public function testIsSaleableOnBundleWithoutSaleableSelectionsOnRequiredOption()
    {
        $productsSku = ['simple1', 'simple2', 'simple3'];
        foreach ($productsSku as $productSku) {
            $product = $this->productRepository->get($productSku);
            $product->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED);
            $this->productRepository->save($product);
        }

        $bundleProduct = $this->productRepository->get('bundle-product');

        $this->assertFalse(
            $bundleProduct->isSalable(),
            'Bundle product supposed to be non saleable if it has at least one required option with no saleable selections'
        );
    }

    /**
     * check bundle product is NOT saleable if
     * there are not enough qty of selection on required option
     *
     * @magentoDataFixtureBeforeTransaction Magento/Bundle/_files/issaleable_product.php
     * @magentoAppIsolation enabled
     * @covers \Magento\Bundle\Model\Product\Type::isSalable
     */
    public function testIsSaleableOnBundleWithNotEnoughQtyOfSelection()
    {
        $this->setQtyForSelections(['simple1', 'simple2', 'simple3'], 1);

        $bundleProduct = $this->productRepository->get('bundle-product');

        $this->assertFalse(
            $bundleProduct->isSalable(),
            'Bundle product supposed to be non saleable if there are not enough qty of selections on required options'
        );
    }

    /**
     * check bundle product is saleable if
     * all his selections has not selection qty
     *
     * @magentoDataFixture Magento/Bundle/_files/issaleable_product.php
     * @magentoAppIsolation enabled
     * @covers \Magento\Bundle\Model\Product\Type::isSalable
     */
    public function testIsSaleableOnBundleWithoutSelectionQty()
    {
        $this->setQtyForSelections(['simple1', 'simple2', 'simple3', 'simple4', 'simple5'], 1);

        $bundleProduct = $this->productRepository->get('bundle-product', false, null, true);
        $bundleType = $bundleProduct->getTypeInstance();

        /** @var \Magento\Bundle\Model\Product\Type $bundleType */
        $options = $bundleType->getOptionsCollection($bundleProduct);
        $selections = $bundleType->getSelectionsCollection($options->getAllIds(), $bundleProduct);

        foreach ($selections as $link) {
            $link->setSelectionQty(null);
        }

        $bundleProduct->setBundleOptionsData([]);
        $bundleProduct->setBundleSelectionsData([]);
        $this->productRepository->save($bundleProduct);

        $this->assertTrue(
            $bundleProduct->isSalable(),
            'Bundle product supposed to be saleable if all his selections has no selection qty'
        );
    }

    /**
     * check bundle product is saleable if
     * all his selections have selection_can_change_qty = 1
     *
     * @magentoDataFixture Magento/Bundle/_files/issaleable_product.php
     * @magentoAppIsolation enabled
     * @covers \Magento\Bundle\Model\Product\Type::isSalable
     */
    public function testIsSaleableOnBundleWithSelectionCanChangeQty()
    {
        $this->setQtyForSelections(['simple1', 'simple2', 'simple3', 'simple4', 'simple5'], 1);

        $bundleProduct = $this->productRepository->get('bundle-product', false, null, true);
        $bundleType = $bundleProduct->getTypeInstance();

        /** @var \Magento\Bundle\Model\Product\Type $bundleType */
        $options = $bundleType->getOptionsCollection($bundleProduct);
        $selections = $bundleType->getSelectionsCollection($options->getAllIds(), $bundleProduct);

        foreach ($selections as $link) {
            $link->setSelectionCanChangeQty(1);
        }

        $bundleProduct->setBundleOptionsData([]);
        $bundleProduct->setBundleSelectionsData([]);
        $this->productRepository->save($bundleProduct);

        $this->assertTrue(
            $bundleProduct->isSalable(),
            'Bundle product supposed to be saleable if all his selections have selection_can_change_qty = 1'
        );
    }

    private function setQtyForSelections($productsSku, $qty)
    {
        foreach ($productsSku as $productSku) {
            $product = $this->productRepository->get($productSku, false, null, true);
            $ea = $product->getExtensionAttributes();
            $ea->getStockItem()->setQty($qty);
            $this->productRepository->save($product);
        }
    }
}
