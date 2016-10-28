<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Bundle\Model\Product;

/**
 * Test class for \Magento\Bundle\Model\Product\Type (bundle product type)
 *
 * @magentoDataFixture Magento/Bundle/_files/issaleable_product.php
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

        $bundleProduct = $this->productRepository->save($bundleProduct);

        $this->assertFalse(
            $bundleProduct->isSalable(),
            'Bundle product supposed to be non saleable if it has no options'
        );
    }

    /**
     * check bundle product is NOT saleable if it has no selections
     *
     * @magentoAppIsolation enabled
     * @covers \Magento\Bundle\Model\Product\Type::isSalable
     */
    public function testIsSaleableOnBundleWithoutSelections()
    {
        $bundleProduct = $this->productRepository->get('bundle-product', true, null, true);
        $bundleType = $bundleProduct->getTypeInstance();
        /** @var  \Magento\Bundle\Model\LinkManagement $linkManager */
        $linkManager = $this->objectManager->create(\Magento\Bundle\Model\LinkManagement::class);

        /** @var \Magento\Bundle\Model\Product\Type $bundleType */
        $options = $bundleType->getOptionsCollection($bundleProduct);
        $selections = $bundleType->getSelectionsCollection($options->getAllIds(), $bundleProduct);

        foreach ($selections as $link) {
            /** @var \Magento\Bundle\Model\Selection $link */
            $linkManager->removeChild('bundle-product', $link->getOptionId(), $link->getSku());
        }

        $bundleProduct = $this->productRepository->get('bundle-product', false, null, true);
        $this->assertFalse(
            $bundleProduct->isSalable(),
            'Bundle product supposed to be non saleable if it has no selections'
        );
    }

    /**
     * check bundle product is NOT saleable if
     * all his selections are not saleable
     *
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
     * all his selections have selection_can_change_qty = 1
     *
     * @magentoAppIsolation enabled
     * @covers \Magento\Bundle\Model\Product\Type::isSalable
     */
    public function testIsSaleableOnBundleWithSelectionCanChangeQty()
    {
        $this->setQtyForSelections(['simple1', 'simple2', 'simple3', 'simple4', 'simple5'], 1);
        $bundleProduct = $this->productRepository->get('bundle-product');
        $options = $bundleProduct->getExtensionAttributes()->getBundleProductOptions();

        foreach ($options as $productOption) {
            $links = $productOption->getProductLinks();
            foreach ($links as $link) {
                $link->setSelectionCanChangeQuantity(1);
            }

            $productOption->setProductLinks($links);
        }

        $extension = $bundleProduct->getExtensionAttributes();
        $extension->setBundleProductOptions($options);
        $bundleProduct->setExtensionAttributes($extension);

        $bundleProduct = $this->productRepository->save($bundleProduct);

        $this->assertTrue(
            $bundleProduct->isSalable(),
            'Bundle product supposed to be saleable if all his selections have selection_can_change_qty = 1'
        );
    }

    /**
     * check bundle product is not saleable if
     * all his options are not required and selections are not saleable
     *
     * @magentoAppIsolation enabled
     * @covers \Magento\Bundle\Model\Product\Type::isSalable
     */
    public function testIsSaleableOnBundleWithoutRequiredOptions()
    {
        // making selections as not saleable
        $productsSku = ['simple1', 'simple2', 'simple3', 'simple4', 'simple5'];
        foreach ($productsSku as $productSku) {
            $product = $this->productRepository->get($productSku);
            $product->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED);
            $this->productRepository->save($product);
        }

        $bundleProduct = $this->productRepository->get('bundle-product');

        // setting all options as not required
        $options = $bundleProduct->getExtensionAttributes()->getBundleProductOptions();
        foreach ($options as $productOption) {
            $productOption->setRequired(false);
        }

        $extension = $bundleProduct->getExtensionAttributes();
        $extension->setBundleProductOptions($options);
        $bundleProduct->setExtensionAttributes($extension);
        $bundleProduct = $this->productRepository->save($bundleProduct);

        $this->assertFalse(
            $bundleProduct->isSalable(),
            'Bundle product supposed to be not saleable if all his options are not required and selections are not saleable'
        );
    }

    /**
     * check bundle product is saleable if
     * it has at least one not required option with saleable selections
     *
     * @magentoAppIsolation enabled
     * @covers \Magento\Bundle\Model\Product\Type::isSalable
     */
    public function testIsSaleableOnBundleWithOneSaleableSelection()
    {
        // making selections as not saleable except simple3
        $productsSku = ['simple1', 'simple2', 'simple4', 'simple5'];

        foreach ($productsSku as $productSku) {
            $product = $this->productRepository->get($productSku);
            $product->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED);
            $this->productRepository->save($product);
        }

        $bundleProduct = $this->productRepository->get('bundle-product');

        // setting all options as not required
        $options = $bundleProduct->getExtensionAttributes()->getBundleProductOptions();
        foreach ($options as $productOption) {
            $productOption->setRequired(false);
        }

        $extension = $bundleProduct->getExtensionAttributes();
        $extension->setBundleProductOptions($options);
        $bundleProduct->setExtensionAttributes($extension);

        $bundleProduct = $this->productRepository->save($bundleProduct);

        $this->assertTrue(
            $bundleProduct->isSalable(),
            'Bundle product supposed to be saleable if it has at least one not required option with saleable selection'
        );
    }

    private function setQtyForSelections($productsSku, $qty)
    {
        foreach ($productsSku as $productSku) {
            $product = $this->productRepository->get($productSku);
            $ea = $product->getExtensionAttributes();
            $ea->getStockItem()->setQty($qty);
            $this->productRepository->save($product);
        }
    }
}
