<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdminAdobeIms\App\Action\Plugin;

use Magento\AdminAdobeIms\Controller\Adminhtml\OAuth\ImsCallback;
use Magento\Backend\App\Action\Plugin\Authentication as CoreAuthentication;
use Magento\Backend\App\BackendAppList;
use Magento\Backend\Model\Auth;
use Magento\Backend\Model\UrlInterface;
use Magento\Framework\App\ActionFlag;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Message\ManagerInterface;

class Authentication extends CoreAuthentication
{
    /**
     * @param Auth $auth
     * @param UrlInterface $url
     * @param ResponseInterface $response
     * @param ActionFlag $actionFlag
     * @param ManagerInterface $messageManager
     * @param UrlInterface $backendUrl
     * @param RedirectFactory $resultRedirectFactory
     * @param BackendAppList $backendAppList
     * @param Validator $formKeyValidator
     */
    public function __construct(
        Auth $auth,
        UrlInterface $url,
        ResponseInterface $response,
        ActionFlag $actionFlag,
        ManagerInterface $messageManager,
        UrlInterface $backendUrl,
        RedirectFactory $resultRedirectFactory,
        BackendAppList $backendAppList,
        Validator $formKeyValidator
    ) {
        parent::__construct(
            $auth,
            $url,
            $response,
            $actionFlag,
            $messageManager,
            $backendUrl,
            $resultRedirectFactory,
            $backendAppList,
            $formKeyValidator
        );

        $this->_openActions[] = ImsCallback::ACTION_NAME;
    }
}
