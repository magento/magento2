<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CurrencySymbol\Controller\Adminhtml\System\Currencysymbol;

/**
 * Class \Magento\CurrencySymbol\Controller\Adminhtml\System\Currencysymbol\Save
 *
 */
class Save extends \Magento\CurrencySymbol\Controller\Adminhtml\System\Currencysymbol
{
    /**
     * Save custom Currency symbol
     *
     * @return void
     */
    public function execute()
    {
        $symbolsDataArray = $this->getRequest()->getParam('custom_currency_symbol', null);
        if (is_array($symbolsDataArray)) {
            foreach ($symbolsDataArray as &$symbolsData) {
                /** @var $filterManager \Magento\Framework\Filter\FilterManager */
                $filterManager = $this->_objectManager->get(\Magento\Framework\Filter\FilterManager::class);
                $symbolsData = $filterManager->stripTags($symbolsData);
            }
        }

        try {
            $this->_objectManager->create(\Magento\CurrencySymbol\Model\System\Currencysymbol::class)
                ->setCurrencySymbolsData($symbolsDataArray);
            $this->messageManager->addSuccess(__('You applied the custom currency symbols.'));
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }

        $this->getResponse()->setRedirect($this->_redirect->getRedirectUrl($this->getUrl('*')));
    }
}
