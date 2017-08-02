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
 * @since 2.1.0
 */
class AddDirtyRulesNotice implements ObserverInterface
{
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     * @since 2.1.0
     */
    private $messageManager;

    /**
     * AddDirtyRulesNotice constructor.
     *
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @since 2.1.0
     */
    public function __construct(\Magento\Framework\Message\ManagerInterface $messageManager)
    {
        $this->messageManager = $messageManager;
    }

    /**
     * @param Observer $observer
     * @return void
     * @since 2.1.0
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
