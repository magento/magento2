<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);
namespace Magento\LoginAsCustomer\Controller\Login;

use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\Action;

/**
 * Login as customer proxy page
 * Allows running JavaScript to load customer data to the browser local storage
 */
class Proceed extends Action implements HttpGetActionInterface
{
    /**
     * Proxy page
     *
     * @return ResultInterface
     */
    public function execute():ResultInterface
    {
        /** @var Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->getConfig()->getTitle()->set(__("You are logged in"));
        return $this->resultFactory->create(ResultFactory::TYPE_PAGE);
    }
}
