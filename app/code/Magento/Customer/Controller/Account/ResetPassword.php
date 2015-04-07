<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Controller\Account;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\ForwardFactory;

class ResetPassword extends \Magento\Customer\Controller\Account
{
    /**
     * @var ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * @param Context $context
     * @param Session $customerSession
     * @param PageFactory $resultPageFactory
     * @param ForwardFactory $resultForwardFactory
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        PageFactory $resultPageFactory,
        ForwardFactory $resultForwardFactory
    ) {
        $this->resultForwardFactory = $resultForwardFactory;
        parent::__construct($context, $customerSession, $resultPageFactory);
    }

    /**
     * Display reset forgotten password form
     *
     * User is redirected on this action when he clicks on the corresponding link in password reset confirmation email
     *
     * @return \Magento\Framework\Controller\Result\Forward
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Forward $resultForward */
        $resultForward = $this->resultForwardFactory->create();
        $resultForward->forward('createPassword');
        return $resultForward;
    }
}
