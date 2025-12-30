<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Fixture;

use Exception;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\AddressFactory;
use Magento\Sales\Model\Order\ItemFactory;
use Magento\Sales\Model\Order\PaymentFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Fixture\RevertibleDataFixtureInterface;
use Random\RandomException;

/**
 * Generic order fixture that supports both customer and guest orders.
 */
class PlaceOrderWithCustomerOrGuest implements RevertibleDataFixtureInterface
{
    private const DEFAULT_ADDRESS = [
        'firstname'  => 'John',
        'lastname'   => 'Doe',
        'street'     => ['123 Test Street'],
        'city'       => 'Los Angeles',
        'postcode'   => '90001',
        'country_id' => 'US',
        'telephone'  => '5555555555',
        'region_id'  => 12,
    ];

    private const DEFAULT_ITEM = [
        'sku'        => 'simple',
        'qty'        => 1,
        'price'      => 100.00,
        'base_price' => 100.00,
    ];

    private const DEFAULT_DATA = [
        'increment_id'       => null,
        'state'              => Order::STATE_PROCESSING,
        'status'             => null,
        'subtotal'           => 100.00,
        'base_subtotal'      => 100.00,
        'grand_total'        => 100.00,
        'base_grand_total'   => 100.00,
        'currency'           => 'USD',
        'base_currency'      => 'USD',
        'customer_id'        => null,
        'customer_email'     => 'customer@example.com',
        'customer_firstname' => 'John',
        'customer_lastname'  => 'Doe',
        'customer_is_guest'  => false,
        'billing_address'    => [],
        'shipping_address'   => [],
        'items'              => [],
        'payment_method'     => 'checkmo',
    ];

    /**
     * @param OrderRepositoryInterface $orderRepository
     * @param OrderInterface $orderPrototype
     * @param AddressFactory $addressFactory
     * @param ItemFactory $itemFactory
     * @param PaymentFactory $paymentFactory
     * @param ProductRepositoryInterface $productRepository
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly OrderInterface $orderPrototype,
        private readonly AddressFactory $addressFactory,
        private readonly ItemFactory $itemFactory,
        private readonly PaymentFactory $paymentFactory,
        private readonly ProductRepositoryInterface $productRepository,
        private readonly StoreManagerInterface $storeManager
    ) {
    }

    /**
     * @param array $data
     * @return DataObject|null
     * @throws NoSuchEntityException
     * @throws Exception
     */
    public function apply(array $data = []): ?DataObject
    {
        $data = array_replace_recursive(self::DEFAULT_DATA, $data);

        /** @var Order $order */
        $order = clone $this->orderPrototype;

        $incrementId = $data['increment_id'] ?? sprintf('%09d', random_int(100000000, 999999999));
        $order->setIncrementId($incrementId)
            ->setState($data['state'])
            ->setStatus($data['status'] ?? $order->getConfig()->getStateDefaultStatus($data['state']))
            ->setSubtotal($data['subtotal'])
            ->setBaseSubtotal($data['base_subtotal'])
            ->setGrandTotal($data['grand_total'])
            ->setBaseGrandTotal($data['base_grand_total'])
            ->setOrderCurrencyCode($data['currency'])
            ->setBaseCurrencyCode($data['base_currency'])
            ->setCustomerId($data['customer_id'])
            ->setCustomerEmail($data['customer_email'])
            ->setCustomerFirstname($data['customer_firstname'])
            ->setCustomerLastname($data['customer_lastname'])
            ->setCustomerIsGuest((bool)$data['customer_is_guest'])
            ->setStoreId($this->storeManager->getStore()->getId());

        $billingAddress  = $this->addressFactory->create([
            'data' => array_replace(self::DEFAULT_ADDRESS, $data['billing_address'])
        ]);
        $shippingAddress = $this->addressFactory->create([
            'data' => array_replace(self::DEFAULT_ADDRESS, $data['shipping_address'])
        ]);
        $billingAddress->setAddressType('billing');
        $shippingAddress->setAddressType('shipping')->setId(null);

        $order->setBillingAddress($billingAddress);
        $order->setShippingAddress($shippingAddress);

        $payment = $this->paymentFactory->create();
        $payment->setMethod($data['payment_method']);
        $order->setPayment($payment);

        $items = $data['items'] ?: [self::DEFAULT_ITEM];
        foreach ($items as $itemData) {
            $itemData = array_replace(self::DEFAULT_ITEM, $itemData);
            $product  = $this->productRepository->get($itemData['sku']);

            $orderItem = $this->itemFactory->create();
            $orderItem->setProductId((int)$product->getId())
                ->setQtyOrdered((float)$itemData['qty'])
                ->setPrice((float)$itemData['price'])
                ->setBasePrice((float)$itemData['base_price'])
                ->setRowTotal((float)$itemData['price'] * (float)$itemData['qty'])
                ->setBaseRowTotal((float)$itemData['base_price'] * (float)$itemData['qty'])
                ->setProductType($product->getTypeId())
                ->setName($product->getName())
                ->setSku($product->getSku());

            $order->addItem($orderItem);
        }

        $this->orderRepository->save($order);

        return $order;
    }

    /**
     * @param DataObject $data
     * @return void
     */
    public function revert(DataObject $data): void
    {
        $order = $this->orderRepository->get((int)$data->getEntityId());
        $this->orderRepository->delete($order);
    }
}
