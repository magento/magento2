<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Controller\Adminhtml\Rate;

use Magento\Backend\Model\Session;
use Magento\Backend\Model\View\Result\Page as ResultPage;
use Magento\Tax\Block\Adminhtml\Rate\Form;
use Magento\Tax\Block\Adminhtml\Rate\Toolbar\Save as ToolbarSave;
use Magento\Tax\Controller\Adminhtml\Rate;
use Magento\Tax\Controller\RegistryConstants;

class Add extends Rate
{
    /**
     * Show Add Form
     *
     * @return ResultPage
     */
    public function execute()
    {
        $this->_coreRegistry->register(
            RegistryConstants::CURRENT_TAX_RATE_FORM_DATA,
            $this->_objectManager->get(Session::class)->getFormData(true)
        );

        $resultPage = $this->initResultPage();
        $layout = $resultPage->getLayout();
        $toolbarSaveBlock = $layout->createBlock(ToolbarSave::class)
            ->assign('header', __('Add New Tax Rate'))
            ->assign('form', $layout->createBlock(Form::class, 'tax_rate_form'));

        $resultPage->addBreadcrumb(__('Manage Tax Rates'), __('Manage Tax Rates'), $this->getUrl('tax/rate'))
            ->addBreadcrumb(__('New Tax Rate'), __('New Tax Rate'))
            ->addContent($toolbarSaveBlock);

        $resultPage->getConfig()->getTitle()->prepend(__('Tax Zones and Rates'));
        $resultPage->getConfig()->getTitle()->prepend(__('New Tax Rate'));
        return $resultPage;
    }
}
