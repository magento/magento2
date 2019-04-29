<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Controller\Guest;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Magento\Sales\Helper\Guest as GuestHelper;

/**
 * Class Form
 */
class Form extends \Magento\Framework\App\Action\Action implements HttpGetActionInterface
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var CustomerSession|null
     */
    private $customerSession;

    /**
     * @var GuestHelper|null
     */
    private $guestHelper;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param CustomerSession|null $customerSession
     * @param GuestHelper|null $guestHelper
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        CustomerSession $customerSession = null,
        GuestHelper $guestHelper = null
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->customerSession = $customerSession ?: ObjectManager::getInstance()->get(CustomerSession::class);
        $this->guestHelper = $guestHelper ?: ObjectManager::getInstance()->get(GuestHelper::class);
    }

    /**
     * Order view form page
     *
     * @return Redirect|Page
     */
    public function execute()
    {
        if ($this->customerSession->isLoggedIn()) {
            return $this->resultRedirectFactory->create()->setPath('customer/account/');
        }

        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('Orders and Returns'));
        $this->guestHelper->getBreadcrumbs($resultPage);

        return $resultPage;
    }
}
