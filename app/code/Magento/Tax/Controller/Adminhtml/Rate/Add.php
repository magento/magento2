<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Controller\Adminhtml\Rate;

use Magento\Tax\Controller\RegistryConstants;

class Add extends \Magento\Tax\Controller\Adminhtml\Rate
{
    /**
     * Show Add Form
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $this->_coreRegistry->register(
            RegistryConstants::CURRENT_TAX_RATE_FORM_DATA,
            $this->_objectManager->get('Magento\Backend\Model\Session')->getFormData(true)
        );

        $resultPage = $this->initResultPage();
        $layout = $resultPage->getLayout();
        $toolbarSaveBlock = $layout->createBlock('Magento\Tax\Block\Adminhtml\Rate\Toolbar\Save')
            ->assign('header', __('Add New Tax Rate'))
            ->assign('form', $layout->createBlock('Magento\Tax\Block\Adminhtml\Rate\Form', 'tax_rate_form'));

        $resultPage->addBreadcrumb(__('Manage Tax Rates'), __('Manage Tax Rates'), $this->getUrl('tax/rate'))
            ->addBreadcrumb(__('New Tax Rate'), __('New Tax Rate'))
            ->addContent($toolbarSaveBlock);

        $resultPage->getConfig()->getTitle()->prepend(__('Tax Zones and Rates'));
        $resultPage->getConfig()->getTitle()->prepend(__('New Tax Rate'));
        return $resultPage;
    }
}
