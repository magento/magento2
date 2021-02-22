<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Model\Product;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Customer\Model\Group;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Catalog\Model\Product\Price\GetPriceIndexDataByProductId;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Abstract class for bundle product price tests.
 */
abstract class PriceAbstract extends TestCase
{
    /** @var ObjectManagerInterface */
    protected $objectManager;

    /** @var Price */
    protected $priceModel;

    /** @var ProductRepositoryInterface */
    protected $productRepository;

    /** @var GetPriceIndexDataByProductId */
    private $getPriceIndexDataByProductId;

    /** @var SerializerInterface */
    private $json;

    /** @var StoreManagerInterface */
    private $storeManager;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->priceModel = $this->objectManager->get(Price::class);
        $this->productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $this->productRepository->cleanCache();
        $this->getPriceIndexDataByProductId = $this->objectManager->get(GetPriceIndexDataByProductId::class);
        $this->json = $this->objectManager->get(SerializerInterface::class);
        $this->storeManager = $this->objectManager->get(StoreManagerInterface::class);
    }
    /**
     * Check bundle prices from index table and final bundle option price.
     *
     * @param string $sku
     * @param array $indexPrices
     * @param array $expectedPrices
     * @return void
     */
    public function checkBundlePrices(string $sku, array $indexPrices, array $expectedPrices): void
    {
        $product = $this->productRepository->get($sku, false, $this->storeManager->getStore()->getId(), true);
        $this->assertIndexTableData((int)$product->getId(), $indexPrices);
        $this->assertPriceWithChosenOption($product, $expectedPrices);
    }

    /**
     * Update products.
     *
     * @param array $products
     * @return void
     */
    protected function updateProducts(array $products): void
    {
        foreach ($products as $sku => $updateData) {
            $product = $this->productRepository->get($sku, false, $this->storeManager->getStore()->getId(), true);
            $product->addData($updateData);
            $this->productRepository->save($product);
        }
    }

    /**
     * Asserts price data in index table.
     *
     * @param int $productId
     * @param array $expectedPrices
     * @return void
     */
    private function assertIndexTableData(int $productId, array $expectedPrices): void
    {
        $data = $this->getPriceIndexDataByProductId->execute(
            $productId,
            Group::NOT_LOGGED_IN_ID,
            (int)$this->storeManager->getStore()->getWebsiteId()
        );
        $data = reset($data);
        foreach ($expectedPrices as $column => $price) {
            $this->assertEquals($price, $data[$column]);
        }
    }

    /**
     * Assert bundle final price with chosen option.
     *
     * @param ProductInterface $bundle
     * @param array $expectedPrices
     * @return void
     */
    private function assertPriceWithChosenOption(ProductInterface $bundle, array $expectedPrices): void
    {
        $option = $bundle->getExtensionAttributes()->getBundleProductOptions()[0] ?? null;
        $this->assertNotNull($option);
        foreach ($option->getProductLinks() as $productLink) {
            $bundle->addCustomOption('bundle_selection_ids', $this->json->serialize([$productLink->getId()]));
            $bundle->addCustomOption('selection_qty_' . $productLink->getId(), 1);
            $this->assertEquals(
                round($expectedPrices[$productLink->getSku()], 2),
                round($this->priceModel->getFinalPrice(1, $bundle), 2)
            );
        }
    }
}
