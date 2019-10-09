<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Resolver;

use Magento\Catalog\Api\CategoryLinkManagementInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\GraphQl\Quote\GetMaskedQuoteIdByReservedOrderId;
use Magento\GraphQl\Service\GraphQlRequest;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\SalesRule\Api\RuleRepositoryInterface;
use Magento\SalesRule\Model\Converter\ToModel;
use Magento\SalesRule\Model\Rule;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Place order test with store promotions via GraphQl
 *
 * @magentoAppArea graphql
 * @magentoDbIsolation disabled
 */
class PlaceOrderWithStorePromotionsTest extends TestCase
{
    /** @var GraphQlRequest */
    private $graphQlRequest;

    /** @var GetMaskedQuoteIdByReservedOrderId */
    private $getMaskedQuoteIdByReservedOrderId;

    /** @var \Magento\Framework\ObjectManager\ObjectManager */
    private $objectManager;

    /** @var  ResourceConnection */
    private $resource;

    /** @var  AdapterInterface */
    private $connection;

    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->graphQlRequest = $this->objectManager->create(GraphQlRequest::class);
        $this->getMaskedQuoteIdByReservedOrderId = $this->objectManager
            ->get(GetMaskedQuoteIdByReservedOrderId::class);
        $this->resource = $this->objectManager->get(ResourceConnection::class);
        $this->connection = $this->resource->getConnection();
    }

    /**
     * Test successful place Order with Cart promotions and verify discounts are inserted into
     * quote_item and quote_address tables
     *
     * @magentoDataFixture Magento/Sales/_files/default_rollback.php
     * @magentoDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoDataFixture Magento/SalesRule/_files/cart_rule_product_in_category.php
     * @magentoDataFixture Magento/GraphQl/Quote/_files/guest/create_empty_cart.php
     * @magentoDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     * @magentoDataFixture Magento/GraphQl/Quote/_files/guest/set_guest_email.php
     * @magentoDataFixture Magento/GraphQl/Quote/_files/set_new_shipping_address.php
     * @magentoDataFixture Magento/GraphQl/Quote/_files/set_new_billing_address.php
     * @magentoDataFixture Magento/GraphQl/Quote/_files/set_flatrate_shipping_method.php
     * @magentoDataFixture Magento/GraphQl/Quote/_files/set_checkmo_payment_method.php
     *
     * @return void
     */
    public function testResolvePlaceOrderWithMultipleProductsAndMultipleCartRules(): void
    {
        $serializer = $this->objectManager->get(SerializerInterface::class);
        $categoryId = 56;
        $reservedOrderId = 'test_quote';
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute($reservedOrderId);
        /** @var Rule $rule */
        $rule = $this->getSalesRule('50% Off on Large Orders');
        $salesRuleId = $rule->getRuleId();
        /** @var categoryLinkManagementInterface $categoryLinkManagement */
        $categoryLinkManagement = $this->objectManager->create(CategoryLinkManagementInterface::class);
        $categoryLinkManagement->assignProductToCategories('simple_product', [$categoryId]);

        $query
            = <<<QUERY
mutation {
  placeOrder(input: {cart_id: "{$maskedQuoteId}"}) {
    order {
      order_id
    }
  }
}
QUERY;

        $response = $this->graphQlRequest->send($query);
        $responseContent = $serializer->unserialize($response->getContent());
        $this->assertArrayNotHasKey('errors', $responseContent);
        $this->assertArrayHasKey('data', $responseContent);
        $orderIdFromResponse = $responseContent['data']['placeOrder']['order']['order_id'];
        $this->assertEquals($reservedOrderId, $orderIdFromResponse);

        $selectFromQuoteItem = $this->connection->select()->from($this->resource->getTableName('quote_item'));
        $resultFromQuoteItem = $this->connection->fetchRow($selectFromQuoteItem);
        $serializedCartDiscount = $resultFromQuoteItem['discounts'];

        $this->assertTrue(array_key_exists($salesRuleId, $serializer->unserialize($serializedCartDiscount)));
        $this->assertEquals(
            10,
            json_decode($serializer->unserialize($serializedCartDiscount)[$salesRuleId]['discount'], true)['amount']
        );
        $this->assertEquals(
            'TestRule_Label',
            $serializer->unserialize($serializedCartDiscount)[$salesRuleId]['rule']
        );
        $selectFromQuoteAddress = $this->connection->select()->from($this->resource->getTableName('quote_address'))
        ->where('address_type = "shipping"');
        $resultFromQuoteAddress = $this->connection->fetchRow($selectFromQuoteAddress);

        $this->assertEquals(
            10,
            json_decode(
                $serializer->unserialize(
                    $resultFromQuoteAddress['discounts']
                )
                [$salesRuleId]['discount'],
                true
            )
            ['amount']
        );
        $this->assertEquals(
            10,
            json_decode(
                $serializer->unserialize(
                    $resultFromQuoteAddress['discounts']
                )
                [$salesRuleId]['discount'],
                true
            )
            ['baseAmount']
        );
        $this->assertEquals(
            'TestRule_Label',
            $serializer->unserialize($resultFromQuoteAddress['discounts'])[$salesRuleId]['rule']
        );
    }

    /**
     * Gets rule by name.
     *
     * @param string $name
     * @return \Magento\SalesRule\Model\Rule
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getSalesRule(string $name): Rule
    {
        /** @var SearchCriteriaBuilder $searchCriteriaBuilder */
        $searchCriteriaBuilder = $this->objectManager->get(SearchCriteriaBuilder::class);
        $searchCriteria = $searchCriteriaBuilder->addFilter('name', $name)
            ->create();

        /** @var CartRepositoryInterface $quoteRepository */
        $ruleRepository = $this->objectManager->get(RuleRepositoryInterface::class);
        $items = $ruleRepository->getList($searchCriteria)->getItems();

        $rule = array_pop($items);
        /** @var \Magento\SalesRule\Model\Converter\ToModel $converter */
        $converter = $this->objectManager->get(ToModel::class);

        return $converter->toModel($rule);
    }
}
