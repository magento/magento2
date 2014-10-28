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
        $this->_title->add(__('Tax Zones and Rates'));

        $rateId = (int)$this->getRequest()->getParam('rate');
        $this->_coreRegistry->register(RegistryConstants::CURRENT_TAX_RATE_ID, $rateId);
        try {
            $taxRateDataObject = $this->_taxRateService->getTaxRate($rateId);
        } catch (NoSuchEntityException $e) {
            $this->getResponse()->setRedirect($this->getUrl("*/*/"));
            return;
        }

        $this->_title->add(sprintf("%s", $taxRateDataObject->getCode()));

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
        $this->_view->renderLayout();
    }
}
