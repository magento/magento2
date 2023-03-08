<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Controller\Adminhtml\Rate;

use Magento\Backend\Model\View\Result\Page as ResultPage;
use Magento\Backend\Model\View\Result\Redirect as ResultRedirect;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Tax\Block\Adminhtml\Rate\Form;
use Magento\Tax\Block\Adminhtml\Rate\Toolbar\Save as ToolbarSave;
use Magento\Tax\Controller\Adminhtml\Rate;
use Magento\Tax\Controller\RegistryConstants;
use Magento\Framework\Controller\ResultFactory;

class Edit extends Rate
{
    /**
     * Show Edit Form
     *
     * @return ResultPage|ResultRedirect
     */
    public function execute()
    {
        $rateId = (int)$this->getRequest()->getParam('rate');
        $this->_coreRegistry->register(RegistryConstants::CURRENT_TAX_RATE_ID, $rateId);
        try {
            $taxRateDataObject = $this->_taxRateRepository->get($rateId);
        } catch (NoSuchEntityException $e) {
            /** @var ResultRedirect $resultRedirect */
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            return $resultRedirect->setPath("*/*/");
        }

        $resultPage = $this->initResultPage();
        $layout = $resultPage->getLayout();

        $toolbarSaveBlock = $layout->createBlock(ToolbarSave::class)
            ->assign('header', __('Edit Tax Rate'))
            ->assign(
                'form',
                $layout->createBlock(
                    Form::class,
                    'tax_rate_form'
                )->setShowLegend(true)
            );

        $resultPage->addBreadcrumb(__('Manage Tax Rates'), __('Manage Tax Rates'), $this->getUrl('tax/rate'))
            ->addBreadcrumb(__('Edit Tax Rate'), __('Edit Tax Rate'))
            ->addContent($toolbarSaveBlock);

        $resultPage->getConfig()->getTitle()->prepend(__('Tax Zones and Rates'));
        $resultPage->getConfig()->getTitle()->prepend(sprintf("%s", $taxRateDataObject->getCode()));
        return $resultPage;
    }
}
