<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order\Shipment;

use Magento\Sales\Api\Data\ShipmentInterface;
use Magento\Sales\Exception\DocumentValidationException;
use Magento\Sales\Model\ValidatorInterface;
use Magento\Sales\Model\ValidatorResultInterface;

/**
 * Interface ShipmentValidatorInterface
 */
interface ShipmentValidatorInterface
{
    /**
     * @param ShipmentInterface $shipment
     * @param ValidatorInterface[] $validators
     * @return ValidatorResultInterface
     * @throws DocumentValidationException
     */
    public function validate(ShipmentInterface $shipment, array $validators);
}
