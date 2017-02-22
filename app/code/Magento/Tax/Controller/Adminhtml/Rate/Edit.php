<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Controller\Adminhtml\Rate;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Tax\Controller\RegistryConstants;
use Magento\Framework\Controller\ResultFactory;

class Edit extends \Magento\Tax\Controller\Adminhtml\Rate
{
    /**
     * Show Edit Form
     *
     * @return \Magento\Backend\Model\View\Result\Page|\Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $rateId = (int)$this->getRequest()->getParam('rate');
        $this->_coreRegistry->register(RegistryConstants::CURRENT_TAX_RATE_ID, $rateId);
        try {
            $taxRateDataObject = $this->_taxRateRepository->get($rateId);
        } catch (NoSuchEntityException $e) {
            /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            return $resultRedirect->setPath("*/*/");
        }

        $resultPage = $this->initResultPage();
        $layout = $resultPage->getLayout();

        $toolbarSaveBlock = $layout->createBlock('Magento\Tax\Block\Adminhtml\Rate\Toolbar\Save')
            ->assign('header', __('Edit Tax Rate'))
            ->assign(
                'form',
                $layout->createBlock('Magento\Tax\Block\Adminhtml\Rate\Form', 'tax_rate_form')->setShowLegend(true)
            );

        $resultPage->addBreadcrumb(__('Manage Tax Rates'), __('Manage Tax Rates'), $this->getUrl('tax/rate'))
            ->addBreadcrumb(__('Edit Tax Rate'), __('Edit Tax Rate'))
            ->addContent($toolbarSaveBlock);

        $resultPage->getConfig()->getTitle()->prepend(__('Tax Zones and Rates'));
        $resultPage->getConfig()->getTitle()->prepend(sprintf("%s", $taxRateDataObject->getCode()));
        return $resultPage;
    }
}
