<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Controller\Adminhtml\Design\Config;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

/**
 * Design Config Settings index page action controller
 * @since 2.1.0
 */
class Index extends Action
{
    /**
     * @var PageFactory
     * @since 2.1.0
     */
    protected $resultPageFactory;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @since 2.1.0
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * Design config list action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     * @since 2.1.0
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Magento_Theme::design_config');
        $resultPage->getConfig()->getTitle()->prepend(__('Design Configuration'));

        return $resultPage;
    }

    /**
     * Theme access rights checking
     *
     * @return bool
     * @since 2.1.0
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Config::config_design');
    }
}
