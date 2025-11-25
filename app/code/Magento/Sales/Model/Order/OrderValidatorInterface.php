<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Sales\Model\Order;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Exception\DocumentValidationException;
use Magento\Sales\Model\ValidatorInterface;
use Magento\Sales\Model\ValidatorResultInterface;

/**
 * Interface OrderValidatorInterface
 * @api
 */
interface OrderValidatorInterface
{
    /**
     * @param OrderInterface $entity
     * @param ValidatorInterface[] $validators
     * @return ValidatorResultInterface
     * @throws DocumentValidationException
     */
    public function validate(OrderInterface $entity, array $validators);
}
