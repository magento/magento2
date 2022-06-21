<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdminAdobeIms\Observer;

use Magento\AdminAdobeIms\Model\LogOut;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class AdminLogoutObserver implements ObserverInterface
{
    /**
     * @var LogOut
     */
    private LogOut $logOut;

    /**
     * @param LogOut $logOut
     */
    public function __construct(
        LogOut $logOut
    ) {
        $this->logOut = $logOut;
    }

    /**
     * Perform logout action
     *
     * @param Observer $observer
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(Observer $observer)
    {
        $this->logOut->execute();
        return $this;
    }
}
