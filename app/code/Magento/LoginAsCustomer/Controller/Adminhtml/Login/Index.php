<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomer\Controller\Adminhtml\Login;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Backend\App\Action;

/**
 * Login As Customer log grid action
 * This action can be executed via GET when just visiting Grid page and POST ajax when filtering, sorting grid
 */
class Index extends Action implements HttpGetActionInterface, HttpPostActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_LoginAsCustomer::login_log';

    /**
     * @var \Magento\LoginAsCustomer\Model\Login
     */
    private $loginModel;

    /**
     * Index constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\LoginAsCustomer\Model\Login $loginModel
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\LoginAsCustomer\Model\Login $loginModel
    ) {
        parent::__construct($context);
        $this->loginModel = $loginModel;
    }

    /**
     * Login As Customer log grid action
     *
     * @return ResultInterface
     */
    public function execute():ResultInterface
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
            $resultForward = $this->resultFactory->create(ResultFactory::TYPE_FORWARD);
            $resultForward->forward('grid');
            return $resultForward;
        }

        $this->loginModel->deleteNotUsed();

        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Magento_LoginAsCustomer::login_log')
            ->addBreadcrumb(__('Customer'), __('Login As Customer Log'));
        $resultPage->getConfig()->getTitle()->prepend(__('Login As Customer Log'));

        return $resultPage;
    }
}
