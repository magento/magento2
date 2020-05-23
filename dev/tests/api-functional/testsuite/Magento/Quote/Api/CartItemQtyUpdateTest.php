<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Quote\Api;

use InvalidArgumentException;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\Quote\Model\Quote;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\TestCase\WebapiAbstract;

/**
 * Checks if qty has been updated
 *
 * Class CartItemQtyUpdateTest
 */
class CartItemQtyUpdateTest extends WebapiAbstract
{
    const RESOURCE_PATH = '/V1/carts/mine/items/';

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * Sets up state before test run
     *
     * @return void
     */
    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
    }

    /**
     * Checks if qty has been updated
     *
     * @magentoApiDataFixture Magento/Sales/_files/quote_with_two_products_and_customer.php
     */
    public function testItemQtyUpdate()
    {
        /** @var CustomerTokenServiceInterface $customerTokenService */
        $customerTokenService = $this->objectManager->create(
            CustomerTokenServiceInterface::class
        );
        /** @var Quote $quote */
        $quote = $this->getQuote('test01');
        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = $this->objectManager->create(ProductRepositoryInterface::class);
        $product = $productRepository->get('simple');
        $quoteItem = $quote->getItemByProduct($product);
        $qtyToUpdate = $quoteItem->getQty() + 2;

        $token = $customerTokenService->createCustomerAccessToken(
            'customer@example.com',
            'password'
        );

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH,
                'httpMethod' => Request::HTTP_METHOD_POST,
                'token' => $token
            ]
        ];
        $requestData = [
            'cart_item' => [
                'item_id' => $quoteItem->getId(),
                'quote_id' => $quote->getId(),
                'sku' => $quoteItem->getSku(),
                'qty' => $qtyToUpdate
            ]
        ];

        $updatedCartItem = $this->_webApiCall($serviceInfo, $requestData);

        $this->assertEquals($updatedCartItem['qty'], $qtyToUpdate);
    }

    /**
     * Retrieve quote by given reserved order ID
     *
     * @param string $reservedOrderId
     * @return Quote
     * @throws InvalidArgumentException
     */
    private function getQuote($reservedOrderId)
    {
        /** @var $cart Quote */
        $cart = $this->objectManager->get(Quote::class);
        $cart->load($reservedOrderId, 'reserved_order_id');
        if (!$cart->getId()) {
            throw new InvalidArgumentException('There is no quote with provided reserved order ID.');
        }
        return $cart;
    }
}
