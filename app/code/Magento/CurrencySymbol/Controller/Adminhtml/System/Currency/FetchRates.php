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
namespace Magento\CurrencySymbol\Controller\Adminhtml\System\Currency;

class FetchRates extends \Magento\CurrencySymbol\Controller\Adminhtml\System\Currency
{
    /**
     * Fetch rates action
     *
     * @return void
     * @throws \Exception|\Magento\Framework\Model\Exception
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\Session $backendSession */
        $backendSession = $this->_objectManager->get('Magento\Backend\Model\Session');
        try {
            $service = $this->getRequest()->getParam('rate_services');
            $this->_getSession()->setCurrencyRateService($service);
            if (!$service) {
                throw new \Exception(__('Please specify a correct Import Service.'));
            }
            try {
                /** @var \Magento\Directory\Model\Currency\Import\ImportInterface $importModel */
                $importModel = $this->_objectManager->get(
                    'Magento\Directory\Model\Currency\Import\Factory'
                )->create(
                    $service
                );
            } catch (\Exception $e) {
                throw new \Magento\Framework\Model\Exception(__('We can\'t initialize the import model.'));
            }
            $rates = $importModel->fetchRates();
            $errors = $importModel->getMessages();
            if (sizeof($errors) > 0) {
                foreach ($errors as $error) {
                    $this->messageManager->addWarning($error);
                }
                $this->messageManager->addWarning(
                    __('All possible rates were fetched, please click on "Save" to apply')
                );
            } else {
                $this->messageManager->addSuccess(__('All rates were fetched, please click on "Save" to apply'));
            }

            $backendSession->setRates($rates);
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }
        $this->_redirect('adminhtml/*/');
    }
}
