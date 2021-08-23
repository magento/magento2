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
 * Base logic for relations data providers checks
 */
abstract class AbstractRelationsDataProviderTest extends TestCase
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
     * Assert grid result data
     *
     * @param int $expectedCount
     * @param array $expectedData
     * @param array $actualResult
     * @return void
     */
    protected function assertResult(int $expectedCount, array $expectedData, array $actualResult): void
    {
        $this->assertCount($expectedCount, $actualResult);
        $item = reset($actualResult);
        foreach ($expectedData as $key => $data) {
            $this->assertEquals($item[$key], $data);
        }
    }

    /**
     * Get component provided data
     *
     * @param string $namespace
     * @return array
     */
    protected function getComponentProvidedData(string $namespace): array
    {
        $component = $this->componentFactory->create($namespace);
        $this->prepareChildComponents($component);

        return $component->getContext()->getDataProvider()->getData();
    }

    /**
     * Prepare request
     *
     * @param string $productSku
     * @param string $relatedProductSku
     * @param string $storeCode
     * @return void
     */
    protected function prepareRequest(string $productSku, string $relatedProductSku, string $storeCode): void
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
}
