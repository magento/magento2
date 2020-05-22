<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomerLog\Controller\Adminhtml\Log;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\LoginAsCustomerApi\Api\ConfigInterface;

/**
 * Login as Customer log grid controller.
 */
class Index extends Action implements HttpGetActionInterface
{
    const ADMIN_RESOURCE = 'Magento_LoginAsCustomerLog::login_log';

    /**
     * @var ConfigInterface
     */
    private $config;

    /**.
     * @param Context $context
     * @param ConfigInterface $config
     */
    public function __construct(
        Context $context,
        ConfigInterface $config
    ) {
        parent::__construct($context);
        $this->config = $config;
    }

    /**
     * @inheritdoc
     */
    public function dispatch(RequestInterface $request)
    {
        if (!$this->config->isEnabled() && ($request->getActionName() !== 'noroute')) {
            $this->_forward('noroute');
        }

        return parent::dispatch($request);
    }

    /**
     * @inheritdoc
     */
    public function execute(): ResultInterface
    {
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Magento_LoginAsCustomerLog::login_log')
            ->addBreadcrumb(__('Login as Customer Log'), __('List'));
        $resultPage->getConfig()->getTitle()->prepend(__('Login as Customer Log'));

        return $resultPage;
    }
}
