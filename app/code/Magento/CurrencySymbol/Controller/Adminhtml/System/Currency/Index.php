<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CurrencySymbol\Controller\Adminhtml\System\Currency;

class Index extends \Magento\CurrencySymbol\Controller\Adminhtml\System\Currency
{
    /**
     * Currency management main page
     *
     * @return void
     */
    public function execute()
    {
        $this->_view->loadLayout();
        $this->_setActiveMenu('Magento_CurrencySymbol::system_currency_rates');
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Currency Rates'));
        $this->_addContent(
            $this->_view->getLayout()->createBlock('Magento\CurrencySymbol\Block\Adminhtml\System\Currency')
        );
        $this->_view->renderLayout();
    }
}
