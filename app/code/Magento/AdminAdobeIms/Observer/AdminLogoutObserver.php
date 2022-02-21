<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdminAdobeIms\Observer;

use Magento\AdobeIms\Model\LogOut;
use Magento\Framework\Event\ObserverInterface;

class AdminLogoutObserver implements ObserverInterface
{
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
     *
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $this->logOut->execute();
        return $this;
    }
}