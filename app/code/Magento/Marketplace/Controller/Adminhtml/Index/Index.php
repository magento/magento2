<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Marketplace\Controller\Adminhtml\Index;

/**
 * Class \Magento\Marketplace\Controller\Adminhtml\Index\Index
 *
 * @since 2.0.0
 */
class Index extends \Magento\Marketplace\Controller\Adminhtml\Index
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     * @since 2.0.0
     */
    protected $resultPageFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Page
     * @since 2.0.0
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->getResultPageFactory()->create();
        $resultPage->setActiveMenu('Magento_Marketplace::partners');
        $resultPage->addBreadcrumb(__('Partners'), __('Partners'));
        $resultPage->getConfig()->getTitle()->prepend(__('Magento Marketplace'));

        return $resultPage;
    }

    /**
     * @return \Magento\Framework\View\Result\PageFactory
     * @since 2.0.0
     */
    public function getResultPageFactory()
    {
        return $this->resultPageFactory;
    }
}
