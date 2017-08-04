<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order\Creditmemo;

use Magento\Sales\Api\Data\CreditmemoItemCreationInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\ValidatorResultInterface;

/**
 * Interface ItemCreationValidatorInterface
 * @since 2.1.3
 */
interface ItemCreationValidatorInterface
{
    /**
     * @param CreditmemoItemCreationInterface $item
     * @param array $validators
     * @param OrderInterface|null $context
     * @return ValidatorResultInterface
     * @since 2.1.3
     */
    public function validate(CreditmemoItemCreationInterface $item, array $validators, OrderInterface $context = null);
}
