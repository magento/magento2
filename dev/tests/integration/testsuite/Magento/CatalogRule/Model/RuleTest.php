<?php
/**
 * Copyright 2012 Adobe
 * All Rights Reserved.
 */
namespace Magento\CatalogRule\Model;

use Exception;
use Magento\Catalog\Model\Indexer\Product\Price\Processor as ProductPriceIndexerProcessor;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\CatalogRule\Test\Fixture\Rule as CatalogRuleFixture;
use Magento\Checkout\Model\CartFactory;
use Magento\Customer\Test\Fixture\Customer as CustomerFixture;
use Magento\Framework\ObjectManagerInterface;
use Magento\Indexer\Cron\UpdateMview;
use Magento\Indexer\Test\Fixture\UpdateMview as UpdateMviewCron;
use Magento\Indexer\Test\Fixture\ScheduleMode;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Test\Fixture\AddProductToCart as AddProductToCartFixture;
use Magento\Quote\Test\Fixture\CustomerCart as CustomerCartFixture;
use Magento\CatalogRule\Model\Indexer\Rule\RuleProductProcessor;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\CatalogRule\Api\CatalogRuleRepositoryInterface;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class RuleTest extends TestCase
{
    /** @var ObjectManagerInterface */
    private $objectManager;

    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var CatalogRuleRepositoryInterface
     */
    private $catalogRuleRepository;

    /**
     * @var Rule
     */
    protected $_object;

    /**
     * @var DataFixtureStorage
     */
    private $fixtures;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->fixtures = $this->objectManager->get(DataFixtureStorageManager::class)->getStorage();
        $this->cartRepository = $this->objectManager->get(CartRepositoryInterface::class);
        $this->catalogRuleRepository = $this->objectManager->get(CatalogRuleRepositoryInterface::class);
        $resourceMock = $this->createPartialMock(
            \Magento\CatalogRule\Model\ResourceModel\Rule::class,
            ['getIdFieldName', 'getRulesFromProduct']
        );
        $resourceMock->expects($this->any())->method('getIdFieldName')->willReturn('id');
        $resourceMock->expects(
            $this->any()
        )->method(
            'getRulesFromProduct'
        )->willReturn(
            $this->_getCatalogRulesFixtures()
        );

        $this->_object = $this->objectManager ->create(
            Rule::class,
            ['ruleResourceModel' => $resourceMock]
        );
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testCalcProductPriceRule()
    {
        $product = $this->objectManager->create(
            Product::class
        );
        $this->assertEquals($this->_object->calcProductPriceRule($product, 100), 45);
        $product->setParentId(true);
        $this->assertEquals($this->_object->calcProductPriceRule($product, 50), 50);
    }

    /**
     * Get array with catalog rule data
     *
     * @return array
     */
    protected function _getCatalogRulesFixtures()
    {
        return [
            [
                'action_operator' => 'by_percent',
                'action_amount' => '50.0000',
                'action_stop' => '0'
            ],
            [
                'action_operator' => 'by_percent',
                'action_amount' => '10.0000',
                'action_stop' => '0'
            ]
        ];
    }

    /**
     * Test case where changing in catalog rule price updates the quote price.
     *
     * @throws Exception
     */
    #[
        DbIsolation(false),
        DataFixture(ProductFixture::class, ['type_id' => 'simple', 'price' => 100], as: 'product'),
        DataFixture(
            CatalogRuleFixture::class,
            [
                'name' => '50% Discount Rule',
                'simple_action' => 'by_percent',
                'discount_amount' => 50,
                'conditions' => [],
                'actions' => [],
                'website_ids' => [1],
                'customer_group_ids' => [0, 1],
                'is_active' => 1
            ],
            as: 'catalog_rule'
        ),
        DataFixture(UpdateMviewCron::class, as: 'updateMviewCron'),
        DataFixture(CustomerFixture::class, as: 'customer'),
        DataFixture(CustomerCartFixture::class, ['customer_id' => '$customer.id$'], as: 'cart'),
        DataFixture(
            AddProductToCartFixture::class,
            ['cart_id' => '$cart.id$', 'product_id' => '$product.id$', 'qty' => 1]
        ),
        DataFixture(ScheduleMode::class, ['indexer' => ProductPriceIndexerProcessor::INDEXER_ID]),
        DataFixture(ScheduleMode::class, ['indexer' => RuleProductProcessor::INDEXER_ID])
    ]
    public function testChangeInCatalogPriceRuleUpdatesTheQuote()
    {
        $catalogRule = $this->fixtures->get('catalog_rule');
        $product = $this->fixtures->get('product');
        $cart = $this->fixtures->get('cart');
        $cartDetails = $this->objectManager->create(CartRepositoryInterface::class)
            ->get($cart->getId());
        $items = $cartDetails->getAllItems();

        //verify that the product is added to the cart with the correct price
        $this->assertCount(1, $items);
        $this->assertEquals($items[0]->getProduct()->getId(), $product->getId());
        $this->assertEquals((float) $items[0]->getPrice(), 50.00);

        $catalogRuleDetails = $this->objectManager->create(CatalogRuleRepositoryInterface::class)
            ->get($catalogRule->getId());
        $catalogRuleDetails->setDiscountAmount(40);
        $catalogRuleDetails->setSimpleAction('by_percent');
        $this->catalogRuleRepository->save($catalogRuleDetails);

        /** @var $mViewCron UpdateMview */
        $mViewCron = $this->objectManager->create(UpdateMview::class);
        $mViewCron->execute();

        $updatedCartRepo = $this->objectManager->create(CartRepositoryInterface::class);
        $updatedCartDetails = $updatedCartRepo->get($cart->getId());

        $updatedItems = $updatedCartDetails->getAllItems();

        $this->assertCount(1, $updatedItems);
        $this->assertEquals($updatedItems[0]->getProduct()->getId(), $product->getId());
        $this->assertEquals((float) $updatedItems[0]->getPrice(), 50.00);
    }
}
