<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Usps\Api;

use Magento\Catalog\Model\Product\Type;
use Magento\Framework\DataObject;
use Magento\Framework\HTTP\ZendClient;
use Magento\Framework\HTTP\ZendClientFactory;
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
use Magento\TestFramework\ObjectManager;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

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
     * @var ZendClient|MockObject
     */
    private $httpClient;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->management = $this->objectManager->get(GuestCouponManagementInterface::class);
        $clientFactory = $this->getMockBuilder(ZendClientFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->httpClient = $this->getMockBuilder(ZendClient::class)
            ->disableOriginalConstructor()
            ->getMock();
        $clientFactory->method('create')
            ->willReturn($this->httpClient);

        $this->objectManager->addSharedInstance($clientFactory, ZendClientFactory::class);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown()
    {
        $this->objectManager->removeSharedInstance(ZendClientFactory::class);
    }

    /**
     * Checks a case when coupon is applied for a guest cart and USPS Priority Mail 1-Day configured as free method.
     *
     * @magentoConfigFixture default_store carriers/usps/active 1
     * @magentoConfigFixture default_store carriers/usps/free_method 1
     * @magentoDataFixture Magento/Usps/Fixtures/cart_rule_coupon_free_shipping.php
     * @magentoDataFixture Magento/Quote/_files/is_salable_product.php
     */
    public function testFreeShippingWithCoupon()
    {
        $couponCode = 'IMPHBR852R61';
        $cartId = $this->createGuestCart();

        $request = new DataObject(['body' => file_get_contents(__DIR__ . '/../Fixtures/rates_response.xml')]);
        $this->httpClient->method('request')
            ->willReturn($request);

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
     * @param int $cartId
     * @return array ShippingMethodInterface[]
     */
    private function estimateShipping(string $cartId)
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
