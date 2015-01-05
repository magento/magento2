<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Backend\Controller\Adminhtml\Auth;

class Logout extends \Magento\Backend\Controller\Adminhtml\Auth
{
    /**
     * @var \Magento\Backend\Model\View\Result\RedirectFactory
     */
    protected $resultRedirectFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Backend\Model\View\Result\RedirectFactory $resultRedirectFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Backend\Model\View\Result\RedirectFactory $resultRedirectFactory
    ) {
        parent::__construct($context);
        $this->resultRedirectFactory = $resultRedirectFactory;
    }

    /**
     * Administrator logout action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $this->_auth->logout();
        $this->messageManager->addSuccess(__('You have logged out.'));

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath($this->_helper->getHomePageUrl());
    }
}
