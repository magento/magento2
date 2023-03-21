<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Model\Sales\Order;

use Magento\Bundle\Model\Sales\Order\Shipment\BundleShipmentTypeValidator;
use \Laminas\Validator\ValidatorInterface;
use Magento\Framework\Phrase;
use Magento\Framework\Webapi\Request;
use Magento\Sales\Model\Order\Shipment;

/**
 * Validate if requested order items can be shipped according to bundle product shipment type
 */
class BundleOrderTypeValidator extends BundleShipmentTypeValidator implements ValidatorInterface
{
    private const SHIPMENT_API_ROUTE = 'v1/shipment';

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
        if (false === $this->canValidate()) {
            return true;
        }

        foreach ($value->getOrder()->getAllItems() as $orderItem) {
            foreach ($value->getItems() as $shipmentItem) {
                if ($orderItem->getItemId() == $shipmentItem->getOrderItemId()) {
                    if ($validationMessages = $this->validate($orderItem)) {
                        $this->renderValidationMessages($validationMessages);
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
     * Determines if the validation should be triggered or not
     *
     * @return bool
     */
    private function canValidate(): bool
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
    }
}
