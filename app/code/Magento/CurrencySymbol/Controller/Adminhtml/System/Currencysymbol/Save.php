<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CurrencySymbol\Controller\Adminhtml\System\Currencysymbol;

use Magento\Backend\Model\View\Result\Redirect;
use Magento\CurrencySymbol\Controller\Adminhtml\System\Currencysymbol as CurrencysymbolController;
use Magento\CurrencySymbol\Model\System\Currencysymbol;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Filter\FilterManager;

/**
 * Class Save
 */
class Save extends CurrencysymbolController implements HttpPostActionInterface
{
    /**
     * Save custom Currency symbol
     *
     * @return ResultInterface
     */
    public function execute()
    {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $symbolsDataArray = $this->getRequest()->getParam('custom_currency_symbol', null);
        if (is_array($symbolsDataArray)) {
            foreach ($symbolsDataArray as &$symbolsData) {
                /** @var $filterManager FilterManager */
                $filterManager = $this->_objectManager->get(FilterManager::class);
                $symbolsData = $filterManager->stripTags($symbolsData);
            }
        }

        try {
            $this->_objectManager->create(Currencysymbol::class)
                ->setCurrencySymbolsData($symbolsDataArray);
            $this->messageManager->addSuccessMessage(__('You applied the custom currency symbols.'));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        return $resultRedirect->setPath('*');
    }
}
