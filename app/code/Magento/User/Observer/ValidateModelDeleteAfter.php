<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\User\Observer;

use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\User\Model\User;

/**
 * Class used to log details of user deletions in action reports.
 */
class ValidateModelDeleteAfter implements ObserverInterface
{
    /**
     * @var ManagerInterface
     */
    private $_eventManager;

    /**
     * Constructor
     *
     * @param ManagerInterface $eventManager
     */
    public function __construct(
        ManagerInterface $eventManager
    ) {
        $this->_eventManager = $eventManager;
    }

    /**
     * Execute observer
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer): void
    {
        /** @var User $deletedUser */
        $deletedUser = $observer->getEvent()->getData('deletedUser');
        /** @var User $model */
        $model = $observer->getEvent()->getData('model');
        if ($deletedUser && $model) {
            $model->setData($deletedUser->getData());
            if ($model->getOrigData() === null) {
                foreach ($model->getData() as $key => $val) {
                    $model->setOrigData($key, $val);
                }
            }
            $this->_eventManager->dispatch('model_delete_after', ['object' => $model]);
        }
    }
}
