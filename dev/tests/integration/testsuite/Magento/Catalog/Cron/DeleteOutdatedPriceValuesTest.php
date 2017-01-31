<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Cron;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Observer\SwitchPriceAttributeScopeOnConfigChange;
use Magento\Framework\App\Config\MutableScopeConfigInterface;
use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
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
     * @magentoAppIsolation enabled
     */
    public function testExecute()
    {
        $secondStoreId = $this->store->load('fixture_second_store')->getId();
        /** @var \Magento\Catalog\Model\Product\Action $productAction */
        $productAction = $this->objectManager->create(
            \Magento\Catalog\Model\Product\Action::class
        );
        /** @var ReinitableConfigInterface $reinitiableConfig */
        $reinitiableConfig = $this->objectManager->get(ReinitableConfigInterface::class);
        $reinitiableConfig->setValue(
            'catalog/price/scope',
            \Magento\Store\Model\Store::PRICE_SCOPE_WEBSITE
        );
        $observer = $this->objectManager->get(\Magento\Framework\Event\Observer::class);
        $this->objectManager->get(SwitchPriceAttributeScopeOnConfigChange::class)
            ->execute($observer);

        $reflection = new \ReflectionClass(\Magento\Catalog\Model\Attribute\ScopeOverriddenValue::class);
        $paths = $reflection->getProperty('attributesValues');
        $paths->setAccessible(true);
        $paths->setValue($this->objectManager->get(\Magento\Catalog\Model\Attribute\ScopeOverriddenValue::class), null);
        $paths->setAccessible(false);

        $product = $this->productRepository->get('simple');
        $productResource = $this->objectManager->create(\Magento\Catalog\Model\ResourceModel\Product::class);

        $productId = $product->getId();
        $productAction->updateWebsites(
            [$productId],
            [$this->store->load('fixture_second_store')->getWebsiteId()],
            'add'
        );
        $product->setStoreId($secondStoreId);
        $product->setPrice(9.99);

        $productResource->save($product);
        $attribute = $this->objectManager->get(\Magento\Eav\Model\Config::class)
            ->getAttribute(
                'catalog_product',
                'price'
            );
        $this->assertEquals(
            '9.99',
            $productResource->getAttributeRawValue($productId, $attribute->getId(), $secondStoreId)
        );
        /** @var MutableScopeConfigInterface $config */
        $config = $this->objectManager->get(
            MutableScopeConfigInterface::class
        );
        $config->setValue(
            \Magento\Store\Model\Store::XML_PATH_PRICE_SCOPE,
            \Magento\Store\Model\Store::PRICE_SCOPE_GLOBAL,
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT
        );
        /** @var \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig */
        $this->cron->execute();
        $this->assertEquals(
            '10.0000',
            $productResource->getAttributeRawValue($productId, $attribute->getId(), $secondStoreId)
        );
    }

    public function tearDown()
    {
        parent::tearDown();
        /** @var ReinitableConfigInterface $reinitiableConfig */
        $reinitiableConfig = $this->objectManager->get(ReinitableConfigInterface::class);
        $reinitiableConfig->setValue(
            'catalog/price/scope',
            \Magento\Store\Model\Store::PRICE_SCOPE_GLOBAL
        );
        $observer = $this->objectManager->get(\Magento\Framework\Event\Observer::class);
        $this->objectManager->get(SwitchPriceAttributeScopeOnConfigChange::class)
            ->execute($observer);
    }
}
