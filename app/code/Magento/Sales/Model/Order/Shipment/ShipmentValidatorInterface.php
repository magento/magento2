<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order\Shipment;

use Magento\Sales\Api\Data\ShipmentInterface;
use Magento\Sales\Exception\DocumentValidationException;
use Magento\Sales\Model\ValidatorInterface;

/**
 * Interface ShipmentValidatorInterface
 */
interface ShipmentValidatorInterface
{
    /**
     * @param ShipmentInterface $shipment
     * @param ValidatorInterface[] $validators
     * @return string[]
     * @throws DocumentValidationException
     */
    public function validate(ShipmentInterface $shipment, array $validators);
}
