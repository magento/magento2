<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Usps\Api;

use Magento\Catalog\Model\Product\Type;
use Magento\Framework\HTTP\AsyncClient\Response;
use Magento\Framework\HTTP\AsyncClientInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\AddressInterfaceFactory;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Api\Data\CartItemInterfaceFactory;
use Magento\Quote\Api\Data\ShippingMethodInterface;
use Magento\Quote\Api\GuestCartItemRepositoryInterface;
use Magento\Quote\Api\GuestCartManagementInterface;
use Magento\Quote\Api\GuestCouponManagementInterface;
use Magento\Quote\Api\GuestShipmentEstimationInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\HTTP\AsyncClientInterfaceMock;
use Magento\TestFramework\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class GuestCouponManagementTest extends TestCase
{
    /**
     * @var GuestCouponManagementInterface
     */
    private $management;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var AsyncClientInterfaceMock
     */
    private $httpClient;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->management = $this->objectManager->get(GuestCouponManagementInterface::class);
        $this->httpClient = $this->objectManager->get(AsyncClientInterface::class);
    }

    /**
     * Checks a case when coupon is applied for a guest cart and USPS Priority Mail 1-Day configured as free method.
     *
     * @magentoConfigFixture default_store carriers/usps/active 1
     * @magentoConfigFixture default_store carriers/usps/free_method 1
     * @magentoDataFixture Magento/Usps/Fixtures/cart_rule_coupon_free_shipping.php
     * @magentoDataFixture Magento/Quote/_files/is_salable_product.php
     * @return void
     */
    public function testFreeShippingWithCoupon(): void
    {
        $couponCode = 'IMPHBR852R61';
        $cartId = $this->createGuestCart();

        //phpcs:disable
        $this->httpClient->nextResponses(
            [
                new Response(200, [], file_get_contents(__DIR__ . '/../Fixtures/rates_response.xml'))
            ]
        );
        //phpcs:enable

        self::assertTrue($this->management->set($cartId, $couponCode));

        $methods = $this->estimateShipping($cartId);
        $methods = $this->filterFreeShippingMethods($methods);
        self::assertEquals(['Fixed', 'Priority Mail 1-Day'], $methods);
    }

    /**
     * Creates guest shopping cart.
     *
     * @return string
     */
    private function createGuestCart(): string
    {
        /** @var GuestCartManagementInterface $cartManagement */
        $cartManagement = $this->objectManager->get(GuestCartManagementInterface::class);
        $cartId = $cartManagement->createEmptyCart();

        /** @var CartItemInterfaceFactory $cartItemFactory */
        $cartItemFactory = $this->objectManager->get(CartItemInterfaceFactory::class);

        /** @var CartItemInterface $cartItem */
        $cartItem = $cartItemFactory->create();
        $cartItem->setQuoteId($cartId);
        $cartItem->setQty(1);
        $cartItem->setSku('simple-99');
        $cartItem->setProductType(Type::TYPE_SIMPLE);

        /** @var GuestCartItemRepositoryInterface $itemRepository */
        $itemRepository = $this->objectManager->get(GuestCartItemRepositoryInterface::class);
        $itemRepository->save($cartItem);

        return $cartId;
    }

    /**
     * Estimates shipment for guest cart.
     *
     * @param string $cartId
     * @return array ShippingMethodInterface[]
     */
    private function estimateShipping(string $cartId): array
    {
        $addressFactory = $this->objectManager->get(AddressInterfaceFactory::class);
        /** @var AddressInterface $address */
        $address = $addressFactory->create();
        $address->setCountryId('US');
        $address->setRegionId(12);
        $address->setPostcode(90230);

        /** @var GuestShipmentEstimationInterface $estimation */
        $estimation = $this->objectManager->get(GuestShipmentEstimationInterface::class);
        return $estimation->estimateByExtendedAddress($cartId, $address);
    }

    /**
     * Filters free shipping methods.
     *
     * @param array $methods
     * @return array
     */
    private function filterFreeShippingMethods(array $methods): array
    {
        $result = [];
        /** @var ShippingMethodInterface $method */
        foreach ($methods as $method) {
            if ($method->getAmount() == 0) {
                $result[] = $method->getMethodTitle();
            }
        }
        return $result;
    }
}
