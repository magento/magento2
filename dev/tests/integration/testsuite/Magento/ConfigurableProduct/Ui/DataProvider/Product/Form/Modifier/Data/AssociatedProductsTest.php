<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Ui\DataProvider\Product\Form\Modifier\Data;

use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Filters\FilterModifier;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\ConfigurableProduct\Ui\DataProvider\Product\Form\Modifier\ConfigurablePanel;
use Magento\Framework\App\RequestInterface;
use PHPUnit\Framework\TestCase;

/**
 * Test verifies modifier for configurable associated product
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AssociatedProductsTest extends TestCase
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface $objectManager
     */
    private $objectManager;

    /**
     * @var \Magento\Framework\Registry $registry
     */
    private $registry;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->registry = $this->objectManager->get(\Magento\Framework\Registry::class);
    }

    /**
     * @dataProvider getProductMatrixDataProvider
     * @param string $interfaceLocale
     * @magentoDataFixture Magento/ConfigurableProduct/_files/product_configurable.php
     * @magentoAppArea adminhtml
     */
    public function testGetProductMatrix($interfaceLocale)
    {
        $productSku = 'configurable';
        $associatedProductsData = [
            [10 => '10.000000'],
            [20 => '20.000000']
        ];
        /** @var \Magento\Catalog\Api\ProductRepositoryInterface $productRepository */
        $productRepository = $this->objectManager->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        $this->registry->register('current_product', $productRepository->get($productSku));
        /** @var $store \Magento\Store\Model\Store */
        $store = $this->objectManager->create(\Magento\Store\Model\Store::class);
        $store->load('admin');
        $this->registry->register('current_store', $store);
        /** @var \Magento\Framework\Locale\ResolverInterface|\PHPUnit\Framework\MockObject\MockObject $localeResolver */
        $localeResolver = $this->getMockBuilder(\Magento\Framework\Locale\ResolverInterface::class)
            ->onlyMethods(['getLocale'])
            ->getMockForAbstractClass();
        $localeResolver->expects($this->any())->method('getLocale')->willReturn($interfaceLocale);
        $localeCurrency = $this->objectManager->create(
            \Magento\Framework\Locale\CurrencyInterface::class,
            ['localeResolver' => $localeResolver]
        );
        $associatedProducts = $this->objectManager->create(
            AssociatedProducts::class,
            ['localeCurrency' => $localeCurrency]
        );
        foreach ($associatedProducts->getProductMatrix() as $productMatrixId => $productMatrixData) {
            $this->assertEquals(
                $associatedProductsData[$productMatrixId][$productMatrixData['id']],
                $productMatrixData['price']
            );
        }
    }

    /**
     * Tests configurable product won't appear in product listing.
     *
     * Tests configurable product won't appear in configurable associated product listing if its options attribute
     * is not filterable in grid.
     *
     * @return void
     * @magentoDataFixture Magento/ConfigurableProduct/_files/product_configurable.php
     * @magentoAppArea adminhtml
     */
    public function testAddManuallyConfigurationsWithNotFilterableInGridAttribute(): void
    {
        /** @var RequestInterface $request */
        $request = $this->objectManager->get(RequestInterface::class);
        $request->setParams([
            FilterModifier::FILTER_MODIFIER => [
                'test_configurable' => [
                    'condition_type' => 'notnull',
                ],
            ],
            'attributes_codes' => [
                'test_configurable'
            ],
        ]);
        $context = $this->objectManager->create(ContextInterface::class, ['request' => $request]);
        /** @var UiComponentFactory $uiComponentFactory */
        $uiComponentFactory = $this->objectManager->get(UiComponentFactory::class);
        $uiComponent = $uiComponentFactory->create(
            ConfigurablePanel::ASSOCIATED_PRODUCT_LISTING,
            null,
            ['context' => $context]
        );

        foreach ($uiComponent->getChildComponents() as $childUiComponent) {
            $childUiComponent->prepare();
        }

        $dataSource = $uiComponent->getDataSourceData();
        $skus = array_column($dataSource['data']['items'], 'sku');

        $this->assertNotContains(
            'configurable',
            $skus,
            'Only products with specified attribute should be in product list'
        );
    }

    /**
     * Test that ASSOCIATED_PRODUCT_LISTING component uses POST to retrieve data
     *
     * @return void
     * @magentoDataFixture Magento/ConfigurableProduct/_files/product_configurable.php
     * @magentoAppArea adminhtml
     */
    public function testUiComponentAssociatedProductListingConfig()
    {
        /** @var RequestInterface $request */
        $request = $this->objectManager->get(RequestInterface::class);
        $request->setParams([
            FilterModifier::FILTER_MODIFIER => [
                'test_configurable' => [
                    'condition_type' => 'notnull',
                ],
            ],
            'attributes_codes' => [
                'test_configurable'
            ],
        ]);
        $context = $this->objectManager->create(ContextInterface::class, ['request' => $request]);
        /** @var UiComponentFactory $uiComponentFactory */
        $uiComponentFactory = $this->objectManager->get(UiComponentFactory::class);
        $uiComponent = $uiComponentFactory->create(
            ConfigurablePanel::ASSOCIATED_PRODUCT_LISTING,
            null,
            ['context' => $context]
        );

        foreach ($uiComponent->getChildComponents() as $childUiComponent) {
            $childUiComponent->prepare();
        }
        $dataSourceConfig = $uiComponent->getContext()->getDataProvider()->getConfigData();
        $dataSourceRequestConfig = $dataSourceConfig['storageConfig']['requestConfig'];
        $this->assertIsArray($dataSourceRequestConfig);
        $this->assertEquals('POST', $dataSourceRequestConfig['method']);
    }

    /**
     * @return array
     */
    public static function getProductMatrixDataProvider()
    {
        return [
            ['en_US'],
            ['zh_Hans_CN']
        ];
    }
}
