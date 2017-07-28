<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CurrencySymbol\Controller\Adminhtml\System\Currency;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class \Magento\CurrencySymbol\Controller\Adminhtml\System\Currency\FetchRates
 *
 * @since 2.0.0
 */
class FetchRates extends \Magento\CurrencySymbol\Controller\Adminhtml\System\Currency
{
    /**
     * Fetch rates action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @since 2.0.0
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\Session $backendSession */
        $backendSession = $this->_objectManager->get(\Magento\Backend\Model\Session::class);
        try {
            $service = $this->getRequest()->getParam('rate_services');
            $this->_getSession()->setCurrencyRateService($service);
            if (!$service) {
                throw new LocalizedException(__('Please specify a correct Import Service.'));
            }
            try {
                /** @var \Magento\Directory\Model\Currency\Import\ImportInterface $importModel */
                $importModel = $this->_objectManager->get(\Magento\Directory\Model\Currency\Import\Factory::class)
                    ->create($service);
            } catch (\Exception $e) {
                throw new LocalizedException(__('We can\'t initialize the import model.'));
            }
            $rates = $importModel->fetchRates();
            $errors = $importModel->getMessages();
            if (sizeof($errors) > 0) {
                foreach ($errors as $error) {
                    $this->messageManager->addWarning($error);
                }
                $this->messageManager->addWarning(
                    __('Click "Save" to apply the rates we found.')
                );
            } else {
                $this->messageManager->addSuccess(__('Click "Save" to apply the rates we found.'));
            }

            $backendSession->setRates($rates);
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('adminhtml/*/');
    }
}
