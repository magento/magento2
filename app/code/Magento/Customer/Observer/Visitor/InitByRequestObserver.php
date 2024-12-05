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
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class InitByRequestObserver extends AbstractVisitorObserver
{
    /**
     * @var SessionManagerInterface
     */
    private $sessionManager;

    /**
     * @param Visitor $visitor
     * @param SessionManagerInterface $sessionManager
     */
    public function __construct(
        Visitor $visitor,
        SessionManagerInterface $sessionManager
    ) {
        parent::__construct($visitor);
        $this->sessionManager = $sessionManager;
    }

    /**
     * Init visitor by request
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        if ($observer->getRequest()->getFullActionName() === 'customer_account_loginPost') {
            $this->sessionManager->setVisitorData(['do_customer_login' => true]);
        }
        $this->visitor->initByRequest($observer);
    }
}
