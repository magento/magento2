<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Observer\Visitor;

use Magento\Customer\Model\Visitor;
use Magento\Framework\Event\Observer;
use Magento\Framework\Session\SessionManagerInterface;

/**
 * Visitor Observer
 */
class InitByRequestObserver extends AbstractVisitorObserver
{
    /**
     * @var SessionManagerInterface
     */
    private $sessionManager;

    public function __construct(
        Visitor $visitor,
        SessionManagerInterface $sessionManager
    ) {
        parent::__construct($visitor);
        $this->sessionManager = $sessionManager;
    }

    /**
     * initByRequest
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        if ($observer->getRequest()->getFullActionName() === 'customer_account_loginPost') {
            $this->sessionManager->unsVisitorData();
        }
        $this->visitor->initByRequest($observer);
    }
}
