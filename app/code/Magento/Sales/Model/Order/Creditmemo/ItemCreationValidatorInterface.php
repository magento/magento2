<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Sales\Model\Order\Creditmemo;

use Magento\Sales\Api\Data\CreditmemoItemCreationInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\ValidatorResultInterface;

/**
 * Interface ItemCreationValidatorInterface
 * @api
 */
interface ItemCreationValidatorInterface
{
    /**
     * @param CreditmemoItemCreationInterface $item
     * @param array $validators
     * @param OrderInterface|null $context
     * @return ValidatorResultInterface
     */
    public function validate(CreditmemoItemCreationInterface $item, array $validators, ?OrderInterface $context = null);
}
