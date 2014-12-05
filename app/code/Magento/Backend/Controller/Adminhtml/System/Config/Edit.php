<?php
/**
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Backend\Controller\Adminhtml\System\Config;

class Edit extends AbstractScopeConfig
{
    /**
     * @var \Magento\Backend\Model\View\Result\RedirectFactory
     */
    protected $resultRedirectFactory;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Backend\Model\Config\Structure $configStructure
     * @param \Magento\Backend\Controller\Adminhtml\System\ConfigSectionChecker $sectionChecker
     * @param \Magento\Backend\Model\Config $backendConfig
     * @param \Magento\Backend\Model\View\Result\RedirectFactory $resultRedirectFactory
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Backend\Model\Config\Structure $configStructure,
        \Magento\Backend\Controller\Adminhtml\System\ConfigSectionChecker $sectionChecker,
        \Magento\Backend\Model\Config $backendConfig,
        \Magento\Backend\Model\View\Result\RedirectFactory $resultRedirectFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        parent::__construct($context, $configStructure, $sectionChecker, $backendConfig);
        $this->resultRedirectFactory = $resultRedirectFactory;
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

        /** @var $section \Magento\Backend\Model\Config\Structure\Element\Section */
        $section = $this->_configStructure->getElement($current);
        if ($current && !$section->isVisible($website, $store)) {
            /** @var \Magento\Backend\Model\View\Result\Redirect $redirectResult */
            $redirectResult = $this->resultRedirectFactory->create();
            return $redirectResult->setPath('adminhtml/*/', ['website' => $website, 'store' => $store]);
        }

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Magento_Backend::system_config');
        $resultPage->getLayout()->getBlock('menu')->setAdditionalCacheKeyInfo([$current]);
        $resultPage->addBreadcrumb(__('System'), __('System'), $this->getUrl('*\/system'));
        $resultPage->getConfig()->getTitle()->prepend(__('Configuration'));
        return $resultPage;
    }
}
