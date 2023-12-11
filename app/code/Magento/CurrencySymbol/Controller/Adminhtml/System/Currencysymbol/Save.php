<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CurrencySymbol\Controller\Adminhtml\System\Currencysymbol;

use Magento\Backend\App\Action;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\CurrencySymbol\Controller\Adminhtml\System\Currencysymbol as CurrencysymbolController;
use Magento\CurrencySymbol\Model\System\CurrencysymbolFactory;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Filter\FilterManager;

/**
 * Controller to save currency symbol
 */
class Save extends CurrencysymbolController implements HttpPostActionInterface
{
    /**
     * @var FilterManager
     */
    private $filterManager;

    /**
     * @var CurrencysymbolFactory
     */
    private $currencySymbolFactory;

    /**
     * @param Action\Context $context
     * @param FilterManager $filterManager
     * @param CurrencysymbolFactory $currencySymbolFactory
     */
    public function __construct(
        Action\Context $context,
        FilterManager $filterManager,
        CurrencysymbolFactory $currencySymbolFactory
    ) {
        parent::__construct($context);
        $this->filterManager = $filterManager;
        $this->currencySymbolFactory = $currencySymbolFactory;
    }

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
                $symbolsData = $this->filterManager->stripTags($symbolsData);
            }
        }

        try {
            $currencySymbol = $this->currencySymbolFactory->create();
            $currencySymbol->setCurrencySymbolsData($symbolsDataArray);
            $this->messageManager->addSuccessMessage(__('You applied the custom currency symbols.'));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        return $resultRedirect->setPath('adminhtml/*/');
    }
}
