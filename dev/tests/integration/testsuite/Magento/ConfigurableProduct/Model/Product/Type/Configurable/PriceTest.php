<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Model\Product\Type\Configurable;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Type\AbstractType;

/**
 * Class PriceTest
 */
class PriceTest extends \PHPUnit_Framework_TestCase
{
    /** @var  \Magento\Framework\ObjectManagerInterface */
    protected $objectManager;

    /** @var  \Magento\Catalog\Api\Data\ProductCustomOptionInterfaceFactory */
    protected $customOptionFactory;

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
    }

    /**
     * @magentoDataFixture Magento/ConfigurableProduct/_files/tax_rule.php
     * @magentoDataFixture Magento/ConfigurableProduct/_files/product_configurable.php
     */
    public function testGetFinalPrice()
    {
        $this->assertPrice(10);
    }

    /**
     * @magentoConfigFixture current_store tax/display/type 1
     * @magentoDataFixture Magento/ConfigurableProduct/_files/tax_rule.php
     * @magentoDataFixture Magento/ConfigurableProduct/_files/product_configurable.php
     */
    public function testGetFinalPriceExcludingTax()
    {
        $this->assertPrice(10);
    }

    /**
     * @magentoConfigFixture current_store tax/display/type 2
     * @magentoDataFixture Magento/ConfigurableProduct/_files/tax_rule.php
     * @magentoDataFixture Magento/ConfigurableProduct/_files/product_configurable.php
     */
    public function testGetFinalPriceIncludingTax()
    {
        //lowest price of configurable variation + 10%
        $this->assertPrice(11);
    }

    /**
     * @magentoConfigFixture current_store tax/display/type 3
     * @magentoDataFixture Magento/ConfigurableProduct/_files/tax_rule.php
     * @magentoDataFixture Magento/ConfigurableProduct/_files/product_configurable.php
     */
    public function testGetFinalPriceIncludingExcludingTax()
    {
        //lowest price of configurable variation + 10%
        $this->assertPrice(11);
    }

    /**
     * @magentoDataFixture Magento/ConfigurableProduct/_files/tax_rule.php
     * @magentoDataFixture Magento/ConfigurableProduct/_files/product_configurable.php
     */
    public function testGetFinalPriceWithSelectedSimpleProduct()
    {
        $product = $this->getProduct('configurable');
        $product->addCustomOption('simple_product', 20, $this->getProduct('simple_20'));
        $this->assertPrice(20, $product);
    }

    /**
     * @magentoConfigFixture current_store tax/display/type 1
     * @magentoDataFixture Magento/ConfigurableProduct/_files/tax_rule.php
     * @magentoDataFixture Magento/ConfigurableProduct/_files/product_configurable.php
     */
    public function testGetFinalPriceWithCustomOption()
    {
        $product = $this->getProduct('configurable');

        $options = $this->prepareOptions(
            [
                [
                    'option_id' => null,
                    'previous_group' => 'text',
                    'title' => 'Test Field',
                    'type' => 'field',
                    'is_require' => 1,
                    'sort_order' => 0,
                    'price' => 100,
                    'price_type' => 'fixed',
                    'sku' => '1-text',
                    'max_characters' => 100,
                ],
            ],
            $product
        );

        $product->setOptions($options);
        $product->setCanSaveCustomOptions(true);

        /** @var \Magento\Catalog\Api\ProductRepositoryInterface $productRepository */
        $productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $product = $productRepository->save($product);

        $optionId = $product->getOptions()[0]->getId();
        $product->addCustomOption(AbstractType::OPTION_PREFIX . $optionId, 'text');
        $product->addCustomOption('option_ids', $optionId);
        $this->assertPrice(110, $product);
    }

    /**
     * @param array $options
     * @param \Magento\Catalog\Model\Product $product
     * @return \Magento\Catalog\Api\Data\ProductCustomOptionInterface[]
     */
    protected function prepareOptions($options, $product)
    {
        $preparedOptions = [];

        if (!$this->customOptionFactory) {
            $this->customOptionFactory = $this->objectManager->create(
                \Magento\Catalog\Api\Data\ProductCustomOptionInterfaceFactory::class
            );
        }

        foreach ($options as $option) {
            $option = $this->customOptionFactory->create(['data' => $option]);
            $option->setProductSku($product->getSku());

            $preparedOptions[] = $option;
        }

        return $preparedOptions;
    }

    /**
     * Test
     *
     * @param $expectedPrice
     * @param null $product
     * @return void
     */
    protected function assertPrice($expectedPrice, $product = null)
    {
        $product = $product ?: $this->getProduct('configurable');

        /** @var $model \Magento\ConfigurableProduct\Model\Product\Type\Configurable\Price */
        $model = $this->objectManager->create(
            \Magento\ConfigurableProduct\Model\Product\Type\Configurable\Price::class
        );

        // final price is the lowest price of configurable variations
        $this->assertEquals(round($expectedPrice, 2), round($model->getFinalPrice(1, $product), 2));
    }

    /**
     * @param string $sku
     * @return \Magento\Catalog\Api\Data\ProductInterface
     */
    private function getProduct($sku)
    {
        /** @var $productRepository ProductRepositoryInterface */
        $productRepository = $this->objectManager->create(ProductRepositoryInterface::class);
        return $productRepository->get($sku, true, null, true);
    }
}
