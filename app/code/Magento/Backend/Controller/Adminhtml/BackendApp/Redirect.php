<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Controller\Adminhtml\BackendApp;

use Magento\Backend\App\AbstractAction;

/**
 * Controller which handles authentication of backend app and redirects back to set cookie with backend app path
 */
class Redirect extends AbstractAction
{
    /**
     * Array of actions which can be processed without secret key validation
     *
     * @var array
     */
    protected $_publicActions = ['redirect'];

    /**
     * @var \Magento\Backend\App\BackendAppList|null
     */
    private $backendAppList;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Backend\App\BackendAppList $backendAppList
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Backend\App\BackendAppList $backendAppList
    ) {
        parent::__construct($context);
        $this->backendAppList = $backendAppList;
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($this->getRequest()->getParam('app')) {
            $url = $this->getUrl('*/*/*', []) . '?app=' . $this->getRequest()->getParam('app');
            return $resultRedirect->setUrl($url);
        }
        return $resultRedirect->setUrl($this->getUrl('*/index/index'));
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        $backendApp = $this->backendAppList->getBackendApp(
            $this->getRequest()->getParam('app')
        );
        if ($backendApp) {
            return $this->_authorization->isAllowed($backendApp->getAclResource());
        }
        return true;
    }
}
