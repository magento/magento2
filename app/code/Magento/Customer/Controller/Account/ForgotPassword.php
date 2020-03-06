<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Controller\Account;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magento\Framework\Controller\Result\Redirect as ResultRedirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\View\Result\Page as ResultPage;
use Magento\Framework\View\Result\PageFactory;

/**
 * Forgot Password controller
 */
class ForgotPassword implements HttpGetActionInterface
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var RedirectFactory
     */
    private $redirectFactory;

    /**
     * @param Session $customerSession
     * @param PageFactory $resultPageFactory
     * @param RedirectFactory $redirectFactory
     */
    public function __construct(
        Session $customerSession,
        PageFactory $resultPageFactory,
        RedirectFactory $redirectFactory
    ) {
        $this->session = $customerSession;
        $this->resultPageFactory = $resultPageFactory;
        $this->redirectFactory = $redirectFactory;
    }

    /**
     * Forgot customer password page
     *
     * @return ResultRedirect|ResultPage
     */
    public function execute()
    {
        if ($this->session->isLoggedIn()) {
            /** @var ResultRedirect $resultRedirect */
            $resultRedirect = $this->redirectFactory->create();
            $resultRedirect->setPath('*/*/');
            return $resultRedirect;
        }

        /** @var ResultPage $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getLayout()->getBlock('forgotPassword')
            ->setEmailValue($this->session->getForgottenEmail());
        $this->session->unsForgottenEmail();

        return $resultPage;
    }
}
