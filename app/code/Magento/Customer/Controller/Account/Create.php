<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Controller\Account;

use Magento\Customer\Controller\AccountInterface;
use Magento\Customer\Model\Registration;
use Magento\Customer\Model\Registration as RegistrationModel;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Controller\Result\Redirect as ResultRedirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\View\Result\Page as ResultPage;
use Magento\Framework\View\Result\PageFactory;

class Create implements AccountInterface, HttpGetActionInterface
{
    /**
     * @var RegistrationModel
     */
    protected $registration;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var RedirectFactory
     */
    private $resultRedirectFactory;

    /**
     * @param Session $customerSession
     * @param PageFactory $resultPageFactory
     * @param Registration $registration
     * @param RedirectFactory $resultRedirectFactory
     */
    public function __construct(
        Session $customerSession,
        PageFactory $resultPageFactory,
        Registration $registration,
        RedirectFactory $resultRedirectFactory = null
    ) {
        $this->session = $customerSession;
        $this->resultPageFactory = $resultPageFactory;
        $this->registration = $registration;
        $this->resultRedirectFactory = $resultRedirectFactory
            ?? ObjectManager::getInstance()->get(RedirectFactory::class);
    }

    /**
     * Customer register form page
     *
     * @return ResultRedirect|ResultPage
     */
    public function execute()
    {
        if ($this->session->isLoggedIn() || !$this->registration->isAllowed()) {
            /** @var ResultRedirect $resultRedirect */
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('*/*');
            return $resultRedirect;
        }

        /** @var ResultPage $resultPage */
        $resultPage = $this->resultPageFactory->create();
        return $resultPage;
    }
}
