<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Controller\Account;

use Magento\Customer\Model\Registration;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;

class Create extends \Magento\Customer\Controller\Account
{
    /** @var Registration */
    protected $registration;

    /**
     * @param Context $context
     * @param Session $customerSession
     * @param Registration $registration
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        Registration $registration
    ) {
        $this->registration = $registration;
        parent::__construct($context, $customerSession);
    }

    /**
     * Customer register form page
     *
     * @return void
     */
    public function execute()
    {
        if ($this->_getSession()->isLoggedIn() || !$this->registration->isAllowed()) {
            $this->_redirect('*/*');
            return;
        }

        $this->_view->loadLayout();
        $this->_view->getLayout()->initMessages();
        $this->_view->renderLayout();
    }
}
