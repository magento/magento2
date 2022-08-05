<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Controller\Adminhtml\Product\Save;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Customer\Api\Data\GroupInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Message\MessageInterface;
use Magento\TestFramework\TestCase\AbstractBackendController;

/**
 * Test cases for set advanced price to product.
 *
 * @magentoAppArea adminhtml
 * @magentoDbIsolation enabled
 */
class AdvancedPricingTest extends AbstractBackendController
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->productRepository = $this->_objectManager->get(ProductRepositoryInterface::class);
    }

    /**
     * Assert that special price correctly saved to product.
     *
     * @magentoDataFixture Magento/Catalog/_files/product_without_options.php
     *
     * @return void
     */
    public function testAddSpecialPriceToProduct(): void
    {
        $product = $this->productRepository->get('simple');
        $postData = [
            'product' => [
                'special_price' => 8,
            ],
        ];
        $this->assertNull($product->getSpecialPrice());
        $this->dispatchWithData((int)$product->getEntityId(), $postData);
        $product = $this->productRepository->get('simple', false, null, true);
        $this->assertEquals(8, $product->getSpecialPrice());
    }

    /**
     * Assert that tier price correctly saved to product.
     *
     * @magentoDataFixture Magento/Catalog/_files/product_without_options.php
     *
     * @return void
     */
    public function testAddTierPriceToProduct(): void
    {
        $product = $this->productRepository->get('simple');
        $postData = [
            'product' => [
                'tier_price' => [
                    [
                        'website_id' => '0',
                        'cust_group' => GroupInterface::CUST_GROUP_ALL,
                        'price_qty' => '100',
                        'price' => 5,
                        'value_type' => 'fixed',
                    ]
                ],
            ],
        ];
        $this->assertEquals(10, $product->getTierPrice(100));
        $this->dispatchWithData((int)$product->getEntityId(), $postData);
        $product = $this->productRepository->get('simple', false, null, true);
        $this->assertEquals(5, $product->getTierPrice(100));
    }

    /**
     * Dispatch product save with data.
     *
     * @param int $productId
     * @param array $productPostData
     * @return void
     */
    private function dispatchWithData(int $productId, array $productPostData): void
    {
        $this->getRequest()->setPostValue($productPostData);
        $this->getRequest()->setMethod(Http::METHOD_POST);
        $this->dispatch('backend/catalog/product/save/id/' . $productId);
        $this->assertSessionMessages(
            $this->containsEqual('You saved the product.'),
            MessageInterface::TYPE_SUCCESS
        );
    }
}
