<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Controller\Adminhtml\System\Config;

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;

class Edit extends AbstractScopeConfig implements HttpGetActionInterface
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Config\Model\Config\Structure $configStructure
     * @param \Magento\Config\Controller\Adminhtml\System\ConfigSectionChecker $sectionChecker
     * @param \Magento\Config\Model\Config $backendConfig
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Config\Model\Config\Structure $configStructure,
        \Magento\Config\Controller\Adminhtml\System\ConfigSectionChecker $sectionChecker,
        \Magento\Config\Model\Config $backendConfig,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        parent::__construct($context, $configStructure, $sectionChecker, $backendConfig);
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * Edit configuration section
     *
     * @return \Magento\Framework\App\ResponseInterface|void
     */
    public function execute()
    {
        $current = $this->getRequest()->getParam('section');
        $website = $this->getRequest()->getParam('website');
        $store = $this->getRequest()->getParam('store');

        /** @var $section \Magento\Config\Model\Config\Structure\Element\Section */
        $section = $this->_configStructure->getElement($current);
        if ($current && !$section->isVisible($website, $store)) {
            /** @var \Magento\Backend\Model\View\Result\Redirect $redirectResult */
            $redirectResult = $this->resultRedirectFactory->create();
            return $redirectResult->setPath('adminhtml/*/', ['website' => $website, 'store' => $store]);
        }

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Magento_Config::system_config');
        $resultPage->getLayout()->getBlock('menu')->setAdditionalCacheKeyInfo([$current]);
        $resultPage->addBreadcrumb(__('System'), __('System'), $this->getUrl('*\/system'));
        $resultPage->getConfig()->getTitle()->prepend(__('Configuration'));
        return $resultPage;
    }
}
