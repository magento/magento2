<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomer\Controller\Adminhtml\Login;

use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Backend\App\Action;

/**
 * Form to chose store view before login as customer
 */
class Manual extends Action implements HttpGetActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_LoginAsCustomer::login_button';

    /**
     * Chose store view for Login as customer
     *
     * @return ResultInterface
     */
    public function execute():ResultInterface
    {
        /** @var Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Magento_LoginAsCustomer::login_button')
            ->addBreadcrumb(__('Customer'), __('Login As Customer Log'), __('Store View To Login In'));
        $resultPage->getConfig()->getTitle()->prepend(__('Store View To Login In'));

        return $resultPage;
    }
}
