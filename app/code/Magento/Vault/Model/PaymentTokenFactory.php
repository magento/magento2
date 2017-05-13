<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Vault\Model;

use Magento\Vault\Api\Data\PaymentTokenFactoryInterface;

/**
 * Interface PaymentTokenFactory
 * @api
 */
class PaymentTokenFactory implements PaymentTokenFactoryInterface
{

    /**
     * PaymentTokenFactory constructor.
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Create payment token entity
     * @param $type string
     * @return PaymentTokenInterface
     */
    public function create($type)
    {
        return $this->objectManager->create(PaymentTokenInterface::class, $type);
    }
}
