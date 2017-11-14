<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Cron;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\Config\MutableScopeConfigInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class DeleteOutdatedPriceValuesTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Cron\DeleteOutdatedPriceValues
     */
    private $cron;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var \Magento\Store\Model\Store
     */
    private $store;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    public function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->productRepository = $this->objectManager->create(ProductRepositoryInterface::class);
        $this->store = $this->objectManager->create(\Magento\Store\Model\Store::class);
        $this->cron = $this->objectManager->create(\Magento\Catalog\Cron\DeleteOutdatedPriceValues::class);
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoDataFixture Magento/Store/_files/second_website_with_two_stores.php
     * @magentoConfigFixture current_store catalog/price/scope 1
     * @magentoDbIsolation enabled
     */
    public function testExecute()
    {
        $defaultStorePrice = 10.00;
        $secondStorePrice = 9.99;
        $secondStoreId = $this->store->load('fixture_second_store')->getId();
        /** @var \Magento\Catalog\Model\Product\Action $productAction */
        $productAction = $this->objectManager->create(
            \Magento\Catalog\Model\Product\Action::class
        );

        $product = $this->productRepository->get('simple');
        $productResource = $this->objectManager->create(\Magento\Catalog\Model\ResourceModel\Product::class);

        $productId = $product->getId();
        $productAction->updateWebsites(
            [$productId],
            [$this->store->load('fixture_second_store')->getWebsiteId()],
            'add'
        );
        $product->setOrigData();
        $product->setStoreId($secondStoreId);
        $product->setPrice($secondStorePrice);

        $productResource->save($product);
        $attribute = $this->objectManager->get(\Magento\Eav\Model\Config::class)
            ->getAttribute(
                'catalog_product',
                'price'
            );
        $this->assertEquals(
            $secondStorePrice,
            $productResource->getAttributeRawValue($productId, $attribute->getId(), $secondStoreId)
        );
        /** @var MutableScopeConfigInterface $config */
        $config = $this->objectManager->get(
            MutableScopeConfigInterface::class
        );

        $config->setValue(
            \Magento\Store\Model\Store::XML_PATH_PRICE_SCOPE,
            null,
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT
        );
        $this->cron->execute();
        $this->assertEquals(
            $secondStorePrice,
            $productResource->getAttributeRawValue($productId, $attribute->getId(), $secondStoreId)
        );

        $config->setValue(
            \Magento\Store\Model\Store::XML_PATH_PRICE_SCOPE,
            \Magento\Store\Model\Store::PRICE_SCOPE_GLOBAL,
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT
        );
        $this->cron->execute();
        $this->assertEquals(
            $defaultStorePrice,
            $productResource->getAttributeRawValue($productId, $attribute->getId(), $secondStoreId)
        );
    }
}
