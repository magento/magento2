<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogRule\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class AddDirtyRulesNotice
 */
class AddDirtyRulesNotice implements ObserverInterface
{
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    private $messageManager;

    /**
     * AddDirtyRulesNotice constructor.
     *
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     */
    public function __construct(\Magento\Framework\Message\ManagerInterface $messageManager)
    {
        $this->messageManager = $messageManager;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $dirtyRules = $observer->getData('dirty_rules');
        if (!empty($dirtyRules)) {
            if ($dirtyRules->getState()) {
                $this->messageManager->addNotice($observer->getData('message'));
            }
        }
    }
}
