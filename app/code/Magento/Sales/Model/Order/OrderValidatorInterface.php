<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Exception\DocumentValidationException;
use Magento\Sales\Model\ValidatorInterface;

/**
 * Interface OrderValidatorInterface
 */
interface OrderValidatorInterface
{
    /**
     * @param OrderInterface $entity
     * @param ValidatorInterface[] $validators
     * @return string[]
     * @throws DocumentValidationException
     */
    public function validate(OrderInterface $entity, array $validators);
}
