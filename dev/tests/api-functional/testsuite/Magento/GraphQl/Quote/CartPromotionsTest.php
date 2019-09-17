<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Quote;

use Magento\Catalog\Api\CategoryLinkManagementInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Visibility;
use Magento\SalesRule\Api\RuleRepositoryInterface;
use Magento\SalesRule\Model\ResourceModel\Rule\Collection;
use Magento\SalesRule\Model\Rule;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test cases for applying cart promotions to items in cart
 */
class CartPromotionsTest extends GraphQlAbstract
{
    /**
     * Test adding single cart rule to multiple products in a cart
     *
     * @magentoApiDataFixture Magento/Catalog/_files/multiple_products.php
     * @magentoApiDataFixture Magento/SalesRule/_files/rules_category.php
     */
    public function testCartPromotionSingleCartRule()
    {
        $objectManager = Bootstrap::getObjectManager();
        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = $objectManager->get(ProductRepositoryInterface::class);
        /** @var Product $prod2 */
        $prod1 = $productRepository->get('simple1');
        $prod2 = $productRepository->get('simple2');
        $productsInCart = [$prod1, $prod2];
        $prod2->setVisibility(Visibility::VISIBILITY_BOTH);
        $productRepository->save($prod2);
        $skus =['simple1', 'simple2'];
        $categoryId = 66;
        /** @var \Magento\Catalog\Api\CategoryLinkManagementInterface $categoryLinkManagement */
        $categoryLinkManagement = $objectManager->create(CategoryLinkManagementInterface::class);
        foreach ($skus as $sku) {
            $categoryLinkManagement->assignProductToCategories(
                $sku,
                [$categoryId]
            );
        }
        /** @var Collection $ruleCollection */
        $ruleCollection = $objectManager->get(Collection::class);
        $ruleLabels = [];
        /** @var Rule $rule */
        foreach ($ruleCollection as $rule) {
            $ruleLabels =  $rule->getStoreLabels();
        }

        $qty = 2;
        $cartId = $this->createEmptyCart();
        $this->addMultipleSimpleProductsToCart($cartId, $qty, $skus[0], $skus[1]);
        $query = $this->getCartItemPricesQuery($cartId);
        $response = $this->graphQlMutation($query);
        $this->assertCount(2, $response['cart']['items']);
        //validating the line item prices, quantity and discount
        $productsInResponse = array_map(null, $response['cart']['items'], $productsInCart);
        $count = count($productsInCart);
        for ($itemIndex = 0; $itemIndex < $count; $itemIndex++) {
            $this->assertNotEmpty($productsInResponse[$itemIndex]);
            $this->assertResponseFields(
                $productsInResponse[$itemIndex][0],
                [
                    'quantity' => $qty,
                    'prices' => [
                        'row_total' => ['value' => $productsInCart[$itemIndex]->getSpecialPrice()*$qty],
                        'row_total_including_tax' => ['value' => $productsInCart[$itemIndex]->getSpecialPrice()*$qty],
                        'discount' => ['value' => $productsInCart[$itemIndex]->getSpecialPrice()*$qty*0.5],
                        'discounts' => [
                            0 =>[
                                'amount' =>
                                    ['value' => $productsInCart[$itemIndex]->getSpecialPrice()*$qty*0.5],
                                'label' => $ruleLabels[0]
                            ]
                        ]
                    ],
                ]
            );
        }

        /** @var Collection $ruleCollection */
       // $ruleCollection = $objectManager->get(Collection::class);
        /** @var RuleRepositoryInterface $ruleRepository */
       // $ruleRepository = $objectManager->get(RuleRepositoryInterface::class);

        /** @var Rule $rule */
//        foreach ($ruleCollection as $rule) {
//            $ruleName = $rule->getName();
//            if($ruleName === '50% Off on Large Orders'){
//                $ruleId = $rule->getRuleId();
                /** @var \Magento\SalesRule\Model\Data\Rule $salesRule */
//                $salesRule = $ruleRepository->getById($ruleId);
//                $salesRule->setStoreLabels(['store_labels' => 'Test Label']);

//                $salesRule->setStoreLabels([
//                        'store_labels' => [
//                            [
//                                'store_id' => 0,
//                                'store_label' => 'TestRule_Label',
//                            ]
//                        ]
//
//                    ]
//                );
            //    $ruleRepository->save($salesRule);
               // $salesRule->save();
        /** @var Rule $salesRule */
    //    $salesRule = $objectManager->get(Rule::class);
//        $salesRule->setData
//        ([
//                'store_labels' => [0 => '50% discount for products in category']
//            ]
//        );
//        $salesRule->save();


    }

    /**
     * Test adding multiple cart rules to multiple products in a cart
     *
     * @magentoApiDataFixture Magento/Catalog/_files/multiple_products.php
     * @magentoApiDataFixture Magento/SalesRule/_files/rules_category.php
     * @magentoApiDataFixture Magento/SalesRule/_files/cart_rule_10_percent_off_qty_more_than_2_items.php
     */
    public function testCartPromotionsMultipleCartRules()
    {
        $objectManager = Bootstrap::getObjectManager();
        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = $objectManager->get(ProductRepositoryInterface::class);
        /** @var Product $prod2 */
        $prod1 = $productRepository->get('simple1');
        $prod2 = $productRepository->get('simple2');
        $productsInCart = [$prod1, $prod2];
        $prod2->setVisibility(Visibility::VISIBILITY_BOTH);
        $productRepository->save($prod2);
        $skus =['simple1', 'simple2'];
        $categoryId = 66;
        /** @var \Magento\Catalog\Api\CategoryLinkManagementInterface $categoryLinkManagement */
        $categoryLinkManagement = $objectManager->create(CategoryLinkManagementInterface::class);
        foreach ($skus as $sku) {
            $categoryLinkManagement->assignProductToCategories(
                $sku,
                [$categoryId]
            );
        }
        /** @var Collection $ruleCollection */
        $ruleCollection = $objectManager->get(Collection::class);
        $ruleLabels = [];
        /** @var Rule $rule */
        foreach ($ruleCollection as $rule) {
            $ruleLabels[] =  $rule->getStoreLabels();
        }
        $qty = 2;
        $cartId = $this->createEmptyCart();
        $this->addMultipleSimpleProductsToCart($cartId, $qty, $skus[0], $skus[1]);
        $query = $this->getCartItemPricesQuery($cartId);
        $response = $this->graphQlMutation($query);
        $this->assertCount(2, $response['cart']['items']);

        //validating the individual discounts per product and aggregate discount per product
        $productsInResponse = array_map(null, $response['cart']['items'], $productsInCart);
        $count = count($productsInCart);
        for ($itemIndex = 0; $itemIndex < $count; $itemIndex++) {
            $this->assertNotEmpty($productsInResponse[$itemIndex]);
            $lineItemDiscount = $productsInResponse[$itemIndex][0]['prices']['discounts'];
            $expectedTotalDiscountValue = ($productsInCart[$itemIndex]->getSpecialPrice()*$qty*0.5)+($productsInCart[$itemIndex]->getSpecialPrice()*$qty*0.5*0.1);
            $this->assertEquals($productsInCart[$itemIndex]->getSpecialPrice()*$qty*0.5, current($lineItemDiscount)['amount']['value']);
            $this->assertEquals('TestRule_Label', current($lineItemDiscount)['label']);

            $lineItemDiscountValue = next($lineItemDiscount)['amount']['value'];
            $this->assertEquals(round($productsInCart[$itemIndex]->getSpecialPrice()*$qty*0.5)*0.1, $lineItemDiscountValue );
            $this->assertEquals('10% off with two items_Label', end($lineItemDiscount)['label']);
            $actualTotalDiscountValue = $lineItemDiscount[0]['amount']['value'] + $lineItemDiscount[1]['amount']['value'];
            $this->assertEquals(round($expectedTotalDiscountValue,2), $actualTotalDiscountValue);

            //removing the elements from the response so that the rest of the response values can be compared
            unset($productsInResponse[$itemIndex][0]['prices']['discounts']);
            unset($productsInResponse[$itemIndex][0]['prices']['discount']);
            $this->assertResponseFields(
                $productsInResponse[$itemIndex][0],
                [
                    'quantity' => $qty,
                    'prices' => [
                        'row_total' => ['value' => $productsInCart[$itemIndex]->getSpecialPrice()*$qty],
                        'row_total_including_tax' => ['value' => $productsInCart[$itemIndex]->getSpecialPrice()*$qty]
                    ],
                ]
            );
        }
    }

    /**
     * @param string $cartId
     * @return string
     */
    private function getCartItemPricesQuery(string $cartId): string
    {
        return <<<QUERY
{
  cart(cart_id:"{$cartId}"){
    items{
      quantity
      prices{
        row_total{
          value
        }
        row_total_including_tax{
          value
        }
        discount{value}
        discounts{
          amount{value}
          label
        }
      }
      }
    }
  }

QUERY;
    }

    /**
     * @return string
     */
    private function createEmptyCart(): string
    {
        $query = <<<QUERY
mutation {
  createEmptyCart
}
QUERY;
        $response = $this->graphQlMutation($query);
        $cartId = $response['createEmptyCart'];
        return $cartId;
    }

    /**
     * @param string $cartId
     * @param int $sku1
     * @param int $qty
     * @param string $sku2
     */
    private function addMultipleSimpleProductsToCart(string $cartId, int $qty, string $sku1, string $sku2): void
    {
        $query = <<<QUERY
mutation {
  addSimpleProductsToCart(input: {
    cart_id: "{$cartId}", 
    cart_items: [
      {
        data: {
          quantity: $qty
          sku: "$sku1"
        }
      } 
      {
        data: {
          quantity: $qty
          sku: "$sku2"
        }
      }    
    ]
  }
  ) {
    cart {
      items {
        product{sku}
        quantity       
            }
         }
      }
}
QUERY;

        $response = $this->graphQlMutation($query);

        self::assertArrayHasKey('cart', $response['addSimpleProductsToCart']);
        self::assertEquals($qty, $response['addSimpleProductsToCart']['cart']['items'][0]['quantity']);
        self::assertEquals($sku1, $response['addSimpleProductsToCart']['cart']['items'][0]['product']['sku']);
        self::assertEquals($qty, $response['addSimpleProductsToCart']['cart']['items'][1]['quantity']);
        self::assertEquals($sku2, $response['addSimpleProductsToCart']['cart']['items'][1]['product']['sku']);
    }
}
