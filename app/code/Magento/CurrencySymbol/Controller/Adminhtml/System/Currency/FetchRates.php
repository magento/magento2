<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CurrencySymbol\Controller\Adminhtml\System\Currency;

class FetchRates extends \Magento\CurrencySymbol\Controller\Adminhtml\System\Currency
{
    /**
     * Fetch rates action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws \Exception|\Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\Session $backendSession */
        $backendSession = $this->_objectManager->get('Magento\Backend\Model\Session');

        $service = $this->getRequest()->getParam('rate_services');
        $this->_getSession()->setCurrencyRateService($service);
        if (!$service) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Please specify a correct Import Service.'));
        }
        try {
            /** @var \Magento\Directory\Model\Currency\Import\ImportInterface $importModel */
            $importModel = $this->_objectManager->get('Magento\Directory\Model\Currency\Import\Factory')
                ->create($service);
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(__('We can\'t initialize the import model.'));
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
        return $this->getDefaultResult();
    }

    /**
     * {@inheritdoc}
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function getDefaultResult()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('adminhtml/*/');
    }
}
