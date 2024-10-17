<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Model\Sales\Order;

use Magento\Bundle\Model\Sales\Order\Shipment\BundleShipmentTypeValidator;
use \Laminas\Validator\ValidatorInterface;
use Magento\Catalog\Model\Product\Type;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Phrase;
use Magento\Framework\Webapi\Request;
use Magento\Sales\Api\Data\ShipmentItemInterface;
use Magento\Sales\Model\Order\Item;
use Magento\Sales\Model\Order\Shipment;

/**
 * Validate if requested order items can be shipped according to bundle product shipment type
 */
class BundleOrderTypeValidator extends BundleShipmentTypeValidator implements ValidatorInterface
{
    private const SHIPMENT_API_ROUTE = 'v1/shipment';

    public const SHIPMENT_TYPE_TOGETHER = '0';

    public const SHIPMENT_TYPE_SEPARATELY = '1';

    /**
     * @var array
     */
    private array $messages = [];

    /**
     * @var Request
     */
    private Request $request;

    /**
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Validates shipment items based on order item properties
     *
     * @param Shipment $value
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Sales\Exception\DocumentValidationException
     */
    public function isValid($value): bool
    {
        if (false === $this->validationNeeded()) {
            return true;
        }

        $result = $shippingInfo = [];
        foreach ($value->getItems() as $shipmentItem) {
            $shippingInfo[$shipmentItem->getOrderItemId()] = [
                'shipment_info' => $shipmentItem,
                'order_info' => $value->getOrder()->getItemById($shipmentItem->getOrderItemId())
            ];
        }

        foreach ($shippingInfo as $shippingItemInfo) {
            if ($shippingItemInfo['order_info']->getProductType() === Type::TYPE_BUNDLE) {
                $result[] = $this->checkBundleItem($shippingItemInfo, $shippingInfo);
            } elseif ($shippingItemInfo['order_info']->getParentItem() &&
                $shippingItemInfo['order_info']->getParentItem()->getProductType() === Type::TYPE_BUNDLE
            ) {
                $result[] = $this->checkChildItem($shippingItemInfo['order_info'], $shippingInfo);
            }
            $this->renderValidationMessages($result);
        }

        return empty($this->messages);
    }

    /**
     * Returns validation messages
     *
     * @return array|string[]
     */
    public function getMessages(): array
    {
        return $this->messages;
    }

    /**
     * Checks if shipment child item can be processed
     *
     * @param Item $orderItem
     * @param array $shipmentInfo
     * @return Phrase|null
     * @throws NoSuchEntityException
     */
    private function checkChildItem(Item $orderItem, array $shipmentInfo): ?Phrase
    {
        $result = null;
        if ($orderItem->getParentItem()->getProductType() === Type::TYPE_BUNDLE &&
            $orderItem->getParentItem()->getProduct()->getShipmentType() === self::SHIPMENT_TYPE_TOGETHER) {
            $result = __(
                'Cannot create shipment as bundle product "%1" has shipment type "%2". ' .
                '%3 should be shipped instead.',
                $orderItem->getParentItem()->getSku(),
                __('Together'),
                __('Bundle product itself')
            );
        }

        if ($orderItem->getParentItem()->getProductType() === Type::TYPE_BUNDLE &&
            $orderItem->getParentItem()->getProduct()->getShipmentType() === self::SHIPMENT_TYPE_SEPARATELY &&
            false === $this->hasParentInShipping($orderItem, $shipmentInfo)
        ) {
            $result = __(
                'Cannot create shipment as bundle product %1 should be included as well.',
                $orderItem->getParentItem()->getSku()
            );
        }

        return $result;
    }

    /**
     * Checks if bundle item can be processed as a shipment item
     *
     * @param array $shippingItemInfo
     * @param array $shippingInfo
     * @return Phrase|null
     */
    private function checkBundleItem(array $shippingItemInfo, array $shippingInfo): ?Phrase
    {
        $result = null;
        /** @var Item $orderItem */
        $orderItem = $shippingItemInfo['order_info'];
        /** @var ShipmentItemInterface $shipmentItem */
        $shipmentItem = $shippingItemInfo['shipment_info'];

        if ($orderItem->getProduct()->getShipmentType() === self::SHIPMENT_TYPE_TOGETHER &&
            $this->hasChildrenInShipping($shipmentItem, $shippingInfo)
        ) {
            $result = __(
                'Cannot create shipment as bundle product "%1" has shipment type "%2". ' .
                '%3 should be shipped instead.',
                $orderItem->getSku(),
                __('Together'),
                __('Bundle product itself')
            );
        }
        if ($orderItem->getProduct()->getShipmentType() === self::SHIPMENT_TYPE_SEPARATELY &&
            false === $this->hasChildrenInShipping($shipmentItem, $shippingInfo)
        ) {
            $result = __(
                'Cannot create shipment as bundle product "%1" has shipment type "%2". ' .
                'Shipment should also incorporate bundle options.',
                $orderItem->getSku(),
                __('Separately')
            );
        }
        return $result;
    }

    /**
     * Determines if a child shipment item has its corresponding parent in shipment
     *
     * @param Item $childItem
     * @param array $shipmentInfo
     * @return bool
     */
    private function hasParentInShipping(Item $childItem, array $shipmentInfo): bool
    {
        /** @var Item $orderItem */
        foreach (array_column($shipmentInfo, 'order_info') as $orderItem) {
            if (!$orderItem->getParentItemId() &&
                $orderItem->getProductType() === Type::TYPE_BUNDLE &&
                $childItem->getParentItemId() == $orderItem->getItemId()
            ) {
                return true;
            }
        }
        return false;
    }

    /**
     * Determines if a bundle shipment item has at least one child in shipment
     *
     * @param ShipmentItemInterface $bundleItem
     * @param array $shippingInfo
     * @return bool
     */
    private function hasChildrenInShipping(ShipmentItemInterface $bundleItem, array $shippingInfo): bool
    {
        /** @var Item $orderItem */
        foreach (array_column($shippingInfo, 'order_info') as $orderItem) {
            if ($orderItem->getParentItemId() &&
                $orderItem->getParentItemId() == $bundleItem->getOrderItemId()
            ) {
                return true;
            }
        }
        return false;
    }

    /**
     * Determines if the validation should be triggered or not
     *
     * @return bool
     */
    private function validationNeeded(): bool
    {
        return str_contains(strtolower($this->request->getUri()->getPath()), self::SHIPMENT_API_ROUTE);
    }

    /**
     * Creates text based validation messages
     *
     * @param array $validationMessages
     * @return void
     */
    private function renderValidationMessages(array $validationMessages): void
    {
        foreach ($validationMessages as $message) {
            if ($message instanceof Phrase) {
                $this->messages[] = $message->render();
            }
        }
        $this->messages = array_unique($this->messages);
    }
}
