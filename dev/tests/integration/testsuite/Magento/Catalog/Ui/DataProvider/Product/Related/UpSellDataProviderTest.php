<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Ui\DataProvider\Product\Related;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponentInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\TestCase;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Checks up-sell products data provider
 *
 * @see \Magento\Catalog\Ui\DataProvider\Product\Related\UpSellDataProvider
 *
 * @magentoAppArea adminhtml
 * @magentoDbIsolation disabled
 */
class UpSellDataProviderTest extends TestCase
{
    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var UiComponentFactory */
    private $componentFactory;

    /** @var RequestInterface */
    private $request;

    /** @var ProductRepositoryInterface */
    private $productRepository;

    /** @var StoreManagerInterface */
    private $storeManager;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->request = $this->objectManager->get(RequestInterface::class);
        $this->componentFactory = $this->objectManager->get(UiComponentFactory::class);
        $this->productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $this->storeManager = $this->objectManager->get(StoreManagerInterface::class);
    }

    /**
     * @dataProvider productDataProvider
     *
     * @magentoDataFixture Magento/Catalog/_files/products_upsell.php
     * @magentoDataFixture Magento/Catalog/_files/product_with_price_on_second_website.php
     *
     * @param string $storeCode
     * @param float $price
     * @return void
     */
    public function testGetData(string $storeCode, float $price): void
    {
        $this->prepareRequest('simple_with_upsell', 'simple', $storeCode);
        $result = $this->getComponentProvidedData('upsell_product_listing')['items'];
        $this->assertCount(1, $result);
        $item = reset($result);
        $this->assertEquals($item['sku'], 'second-website-price-product');
        $this->assertEquals($price, (float)$item['price']);
    }

    /**
     * @return array
     */
    public function productDataProvider(): array
    {
        return [
            'without_store_code' => [
                'store_code' => 'default',
                'product_price' => 20,
            ],
            'with_store_code' => [
                'store_code' => 'fixture_second_store',
                'product_price' => 10,
            ],
        ];
    }

    /**
     * Get component provided data
     *
     * @param string $namespace
     * @return array
     */
    private function getComponentProvidedData(string $namespace): array
    {
        $component = $this->componentFactory->create($namespace);
        $this->prepareChildComponents($component);

        return $component->getContext()->getDataProvider()->getData();
    }

    /**
     * Call prepare method in the child components
     *
     * @param UiComponentInterface $component
     * @return void
     */
    private function prepareChildComponents(UiComponentInterface $component): void
    {
        foreach ($component->getChildComponents() as $child) {
            $this->prepareChildComponents($child);
        }

        $component->prepare();
    }

    /**
     * Prepare request
     *
     * @param string $productSku
     * @param string $relatedProductSku
     * @param string $storeCode
     * @return void
     */
    private function prepareRequest(string $productSku, string $relatedProductSku, string $storeCode): void
    {
        $storeId = (int)$this->storeManager->getStore($storeCode)->getId();
        $productId = (int)$this->productRepository->get($productSku)->getId();
        $relatedProductId = (int)$this->productRepository->get($relatedProductSku)->getId();
        $params = [
            'current_product_id' => $productId,
            'current_store_id' => $storeId,
            'filters_modifier' => [
                'entity_id' => [
                    'condition_type' => 'nin',
                    'value' => [$relatedProductId],
                ],
            ],
        ];

        $this->request->setParams($params);
    }
}
