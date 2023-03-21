<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Model\Sales\Order;

use Magento\Bundle\Model\Sales\Order\Shipment\BundleShipmentTypeValidator;
use \Laminas\Validator\ValidatorInterface;
use Magento\Sales\Model\Order\Shipment;

/**
 * Validate if requested order items can be shipped according to bundle product shipment type
 */
class BundleOrderTypeValidator extends BundleShipmentTypeValidator implements ValidatorInterface
{
    private const SHIPMENT_API_ROUTE = '/v1/shipment/';

    /**
     * @var array
     */
    private array $messages = [];

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
        if (false === $this->canValidate()) {
            return true;
        }

        foreach ($value->getOrder()->getAllItems() as $orderItem) {
            foreach ($value->getItems() as $shipmentItem) {
                if ($orderItem->getItemId() == $shipmentItem->getOrderItemId()) {
                    if ($result = $this->validate($orderItem)) {
                        $this->messages[] = $result;
                    }
                }
            }
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
     * @return bool
     */
    private function canValidate(): bool
    {
        $request = \Magento\Framework\App\ObjectManager::getInstance()->get(\Magento\Framework\Webapi\Request::class);

        return str_contains(strtolower($request->getUri()->getPath()), self::SHIPMENT_API_ROUTE);
    }
}
