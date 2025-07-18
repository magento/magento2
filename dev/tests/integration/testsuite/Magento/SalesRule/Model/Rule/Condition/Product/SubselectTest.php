<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Model\Rule\Condition\Product;

use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Framework\ObjectManagerInterface;
use Magento\Quote\Test\Fixture\AddProductToCart;
use Magento\Quote\Test\Fixture\CustomerCart;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\SalesRule\Model\Rule\Condition\Product as SalesRuleProduct;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Integration test for Subselect::validate() method returning true.
 *
 * @magentoAppArea frontend
 */
class SubselectTest extends TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var Subselect
     */
    private $subselectCondition;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->subselectCondition = $this->objectManager->create(Subselect::class);
        $this->productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
    }

    /**
     * Test validate() method returning true for total quantity >= 2 with single shipping.
     *
     */
    #[
        DataFixture(Customer::class, as: 'customer'),
        DataFixture(CustomerCart::class, ['customer_id' => '$customer.id$'], 'quote'),
        DataFixture(ProductFixture::class, ['sku' => 'simple1', 'price' => 100.50], as: 'product'),
        DataFixture(
            AddProductToCart::class,
            ['cart_id' => '$quote.id$', 'product_id' => '$product.id$', 'qty' => 2],
            'item'
        ),
    ]
    public function testValidateReturnsTrueForSufficientQuantity()
    {
        $this->subselectCondition->setData([
            'attribute' => 'qty',
            'operator' => '>=',
            'value' => 2,
            'aggregator' => 'all',
        ]);
        $productCondition = $this->objectManager->create(SalesRuleProduct::class);
        $productCondition->setData([
            'attribute' => 'sku',
            'operator' => '==',
            'value' => 'simple1',
        ]);
        $this->subselectCondition->setConditions([$productCondition]);
        $product = $this->productRepository->get('simple1');
        $this->assertNotNull($product->getId());
        $quote = DataFixtureStorageManager::getStorage()->get('quote');
        $quote->setStoreId(1)->setIsActive(true)->setIsMultiShipping(false);
        $this->assertNotNull($quote->getId());
        $this->assertNotEmpty($quote->getAllVisibleItems());
        $quoteItem = $quote->getAllVisibleItems()[0];
        $this->assertNotNull($quoteItem);
        $this->assertNotNull($quoteItem->getProduct());
        $this->assertEquals(2, $quoteItem->getQty());
        $this->assertNotNull($quoteItem->getQuote());
        $result = $this->subselectCondition->validate($quoteItem);
        $this->assertTrue($result);
    }
}
