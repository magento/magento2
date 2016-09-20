<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order\Creditmemo;

use Magento\Sales\Api\Data\CreditmemoItemCreationInterface;
use Magento\Sales\Api\Data\OrderInterface;

/**
 * Interface ItemCreationValidatorInterface
 */
interface ItemCreationValidatorInterface
{
    /**
     * @param CreditmemoItemCreationInterface $item
     * @param array $validators
     * @param OrderInterface|null $context
     * @return mixed
     */
    public function validate(CreditmemoItemCreationInterface $item, array $validators, OrderInterface $context = null);
}
