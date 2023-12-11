<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Model\Order\Shipment;

use Magento\Framework\Exception\ConfigurationMismatchException;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Model\ValidatorResultInterface;

/**
 * Represents shipment creation items validator interface
 */
interface ShipmentItemsValidatorInterface
{
    /**
     * Validates shipment items creations
     *
     * @param OrderItemInterface[] $items
     * @return ValidatorResultInterface
     * @throws ConfigurationMismatchException
     */
    public function validate(array $items): ValidatorResultInterface;
}
