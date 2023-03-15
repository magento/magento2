<?php

namespace Magento\Bundle\Model\Sales\Order;

use Magento\Bundle\Model\Sales\Order\Shipment\BundleShipmentTypeValidator;
use \Laminas\Validator\ValidatorInterface;
use Magento\Sales\Model\Order\Shipment;

class BundleOrderTypeValidator extends BundleShipmentTypeValidator implements ValidatorInterface
{
    /**
     * @var array
     */
    private array $messages = [];

    /**
     * @param Shipment $value
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Sales\Exception\DocumentValidationException
     */
    public function isValid($value): bool
    {
        foreach ($value->getOrder()->getAllItems() as $orderItem) {
            foreach ($value->getItems() as $shipmentItem) {
                if ($orderItem->getItemId() == $shipmentItem->getOrderItemId()) {
                    $this->messages = array_merge($this->messages, $this->validate($orderItem));
                }
            }
        }

        return empty($this->messages);
    }

    /**
     * @return array|string[]
     */
    public function getMessages(): array
    {
        return $this->messages;
    }
}
