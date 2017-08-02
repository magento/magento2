<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Controller\Account;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class \Magento\Customer\Controller\Account\ForgotPassword
 *
 * @since 2.0.0
 */
class ForgotPassword extends \Magento\Customer\Controller\AbstractAccount
{
    /**
     * @var PageFactory
     * @since 2.0.0
     */
    protected $resultPageFactory;

    /**
     * @var Session
     * @since 2.0.0
     */
    protected $session;

    /**
     * @param Context $context
     * @param Session $customerSession
     * @param PageFactory $resultPageFactory
     * @since 2.0.0
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        PageFactory $resultPageFactory
    ) {
        $this->session = $customerSession;
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     * Forgot customer password page
     *
     * @return \Magento\Framework\View\Result\Page
     * @since 2.0.0
     */
    public function execute()
    {
        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getLayout()->getBlock('forgotPassword')->setEmailValue($this->session->getForgottenEmail());

        $this->session->unsForgottenEmail();

        return $resultPage;
    }
}
