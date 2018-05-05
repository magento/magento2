<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Bundle\Model\Product;

/**
 * Test class for \Magento\Bundle\Model\Product\SaveHandler
 * The tested class used indirectly
 *
 * @magentoDataFixture Magento/Bundle/_files/product.php
 * @magentoDataFixture Magento/Store/_files/second_website_with_two_stores.php
 * @magentoDbIsolation disabled
 * @magentoAppIsolation enabled
 */
class SaveHandlerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var \Magento\Store\Model\Store
     */
    private $store;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->store = $this->objectManager->create(\Magento\Store\Model\Store::class);
        /** @var \Magento\Catalog\Api\ProductRepositoryInterface $productRepository */
        $this->productRepository = $this->objectManager->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);
    }

    public function testOptionTitlesOnDifferentStores()
    {
        /**
         * @var \Magento\Bundle\Model\Product\OptionList $optionList
         */
        $optionList = $this->objectManager->create(\Magento\Bundle\Model\Product\OptionList::class);

        $secondStoreId = $this->store->load('fixture_second_store')->getId();
        $thirdStoreId = $this->store->load('fixture_third_store')->getId();

        $product = $this->productRepository->get('bundle-product', true, $secondStoreId, true);
        $options = $optionList->getItems($product);
        $title = $options[0]->getTitle();
        $newTitle = $title . ' ' . $this->store->load('fixture_second_store')->getCode();
        $options[0]->setTitle($newTitle);
        $extension = $product->getExtensionAttributes();
        $extension->setBundleProductOptions($options);
        $product->setExtensionAttributes($extension);
        $product->save();

        $product = $this->productRepository->get('bundle-product', true, $thirdStoreId, true);
        $options = $optionList->getItems($product);
        $newTitle = $title . ' ' . $this->store->load('fixture_third_store')->getCode();
        $options[0]->setTitle($newTitle);
        $extension = $product->getExtensionAttributes();
        $extension->setBundleProductOptions($options);
        $product->setExtensionAttributes($extension);
        $product->save();

        $product = $this->productRepository->get('bundle-product', false, $secondStoreId, true);
        $options = $optionList->getItems($product);
        $this->assertEquals(1, count($options));
        $this->assertEquals(
            $title . ' ' . $this->store->load('fixture_second_store')->getCode(),
            $options[0]->getTitle()
        );
    }
}
