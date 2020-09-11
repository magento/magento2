<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CurrencySymbol\Controller\Adminhtml\System\Currency;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Backend\Model\Session as BackendSession;
use Magento\CurrencySymbol\Controller\Adminhtml\System\Currency as CurrencyAction;
use Magento\Directory\Model\Currency\Import\Factory as CurrencyImportFactory;
use Magento\Directory\Model\Currency\Import\ImportInterface as CurrencyImport;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Registry;
use Exception;

/**
 * Fetch rates controller.
 */
class FetchRates extends CurrencyAction implements HttpGetActionInterface, HttpPostActionInterface
{
    /**
     * @var BackendSession
     */
    private $backendSession;

    /**
     * @var CurrencyImportFactory
     */
    private $currencyImportFactory;

    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * @param Context $context
     * @param Registry $coreRegistry
     * @param BackendSession|null $backendSession
     * @param CurrencyImportFactory|null $currencyImportFactory
     * @param Escaper|null $escaper
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        ?BackendSession $backendSession = null,
        ?CurrencyImportFactory $currencyImportFactory = null,
        ?Escaper $escaper = null
    ) {
        parent::__construct($context, $coreRegistry);
        $this->backendSession = $backendSession ?: ObjectManager::getInstance()->get(BackendSession::class);
        $this->currencyImportFactory = $currencyImportFactory ?: ObjectManager::getInstance()
            ->get(CurrencyImportFactory::class);
        $this->escaper = $escaper ?: ObjectManager::getInstance()->get(Escaper::class);
    }

    /**
     * Fetch rates action
     *
     * @return Redirect
     */
    public function execute()
    {
        try {
            $service = $this->getRequest()->getParam('rate_services');
            $this->_getSession()->setCurrencyRateService($service);
            if (!$service) {
                throw new LocalizedException(__('The Import Service is incorrect. Verify the service and try again.'));
            }
            try {
                /** @var CurrencyImport $importModel */
                $importModel = $this->currencyImportFactory->create($service);
            } catch (Exception $e) {
                throw new LocalizedException(
                    __("The import model can't be initialized. Verify the model and try again.")
                );
            }
            $rates = $importModel->fetchRates();
            $errors = $importModel->getMessages();
            if (count($errors) > 0) {
                foreach ($errors as $error) {
                    $escapedError = $this->escaper->escapeHtml($error);
                    $this->messageManager->addWarningMessage($escapedError);
                }
                $this->messageManager->addWarningMessage(
                    __('Click "Save" to apply the rates we found.')
                );
            } else {
                $this->messageManager->addSuccessMessage(__('Click "Save" to apply the rates we found.'));
            }

            $this->backendSession->setRates($rates);
        } catch (Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('adminhtml/*/');
    }
}
