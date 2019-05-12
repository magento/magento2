<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\User\Cron;

/**
 * Disable expired users.
 */
class DisableExpiredUsers
{

    /**
     * @var \Magento\User\Model\ResourceModel\User\CollectionFactory
     */
    private $collectionFactory;

    /**
     * @param \Magento\User\Model\ResourceModel\User\CollectionFactory $collectionFactory
     */
    public function __construct(
        \Magento\User\Model\ResourceModel\User\CollectionFactory $collectionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Disable all expired user accounts.
     */
    public function execute()
    {
        $users = $this->collectionFactory->create()
            ->addExpiresAtFilter()
            ->addFieldToFilter('is_active', 1)
        ;
        /** @var \Magento\User\Model\User $user */
        foreach ($users as $user) {
            $user->setIsActive(0)
                ->setExpiresAt(null)
                ->save();
        }
    }
}
