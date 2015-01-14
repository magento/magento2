<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Controller\Adminhtml\Rate;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Tax\Controller\RegistryConstants;

class Edit extends \Magento\Tax\Controller\Adminhtml\Rate
{
    /**
     * Show Edit Form
     *
     * @return void
     */
    public function execute()
    {
        $rateId = (int)$this->getRequest()->getParam('rate');
        $this->_coreRegistry->register(RegistryConstants::CURRENT_TAX_RATE_ID, $rateId);
        try {
            $taxRateDataObject = $this->_taxRateRepository->get($rateId);
        } catch (NoSuchEntityException $e) {
            $this->getResponse()->setRedirect($this->getUrl("*/*/"));
            return;
        }

        $this->_initAction()->_addBreadcrumb(
            __('Manage Tax Rates'),
            __('Manage Tax Rates'),
            $this->getUrl('tax/rate')
        )->_addBreadcrumb(
            __('Edit Tax Rate'),
            __('Edit Tax Rate')
        )->_addContent(
            $this->_view->getLayout()->createBlock(
                'Magento\Tax\Block\Adminhtml\Rate\Toolbar\Save'
            )->assign(
                'header',
                __('Edit Tax Rate')
            )->assign(
                'form',
                $this->_view->getLayout()->createBlock(
                    'Magento\Tax\Block\Adminhtml\Rate\Form',
                    'tax_rate_form'
                )->setShowLegend(
                    true
                )
            )
        );
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Tax Zones and Rates'));
        $this->_view->getPage()->getConfig()->getTitle()->prepend(sprintf("%s", $taxRateDataObject->getCode()));
        $this->_view->renderLayout();
    }
}
