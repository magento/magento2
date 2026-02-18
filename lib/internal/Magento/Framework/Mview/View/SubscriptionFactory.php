<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Mview\View;

class SubscriptionFactory extends AbstractFactory
{
    /**
     * Instance name
     */
    const INSTANCE_NAME = SubscriptionInterface::class;

    /**
     * @param array $data
     * @return SubscriptionInterface
     */
    public function create(array $data = [])
    {
        $instanceName = isset($data['subscriptionModel']) ? $data['subscriptionModel'] : self::INSTANCE_NAME;
        unset($data['subscriptionModel']);
        return $this->objectManager->create($instanceName, $data);
    }
}
