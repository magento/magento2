<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdminNotification\Observer;

use Magento\AdminNotification\Model\Feed;
use Magento\AdminNotification\Model\FeedFactory;
use Magento\Backend\Model\Auth\Session;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * AdminNotification observer
 *
 * @package Magento\AdminNotification\Observer
 * @author Magento Core Team <core@magentocommerce.com>
 */
class PredispatchAdminActionControllerObserver implements ObserverInterface
{
    /**
     * @var FeedFactory
     */
    private $feedFactory;

    /**
     * @var Session
     */
    private $backendAuthSession;

    /**
     * @param FeedFactory $feedFactory
     * @param Session $backendAuthSession
     */
    public function __construct(
        FeedFactory $feedFactory,
        Session $backendAuthSession
    ) {
        $this->feedFactory = $feedFactory;
        $this->backendAuthSession = $backendAuthSession;
    }

    /**
     * Predispatch admin action controller
     *
     * @param Observer $observer
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(Observer $observer)
    {
        if ($this->backendAuthSession->isLoggedIn()) {
            $feedModel = $this->feedFactory->create();
            if ($feedModel instanceof Feed) {
                $feedModel->checkUpdate();
            }
        }
    }
}
