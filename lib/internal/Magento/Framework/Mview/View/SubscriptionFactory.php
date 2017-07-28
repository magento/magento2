<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Mview\View;

/**
 * Class \Magento\Framework\Mview\View\SubscriptionFactory
 *
 * @since 2.0.0
 */
class SubscriptionFactory extends AbstractFactory
{
    /**
     * Instance name
     */
    const INSTANCE_NAME = SubscriptionInterface::class;

    /**
     * @param array $data
     * @return SubscriptionInterface
     * @since 2.1.0
     */
    public function create(array $data = [])
    {
        $instanceName = isset($data['subscriptionModel']) ? $data['subscriptionModel'] : self::INSTANCE_NAME;
        unset($data['subscriptionModel']);
        return $this->objectManager->create($instanceName, $data);
    }
}
