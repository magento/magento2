<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Exception\DocumentValidationException;
use Magento\Sales\Model\ValidatorInterface;
use Magento\Sales\Model\ValidatorResultInterface;

/**
 * Interface OrderValidatorInterface
 * @since 2.1.2
 */
interface OrderValidatorInterface
{
    /**
     * @param OrderInterface $entity
     * @param ValidatorInterface[] $validators
     * @return ValidatorResultInterface
     * @throws DocumentValidationException
     * @since 2.1.2
     */
    public function validate(OrderInterface $entity, array $validators);
}
