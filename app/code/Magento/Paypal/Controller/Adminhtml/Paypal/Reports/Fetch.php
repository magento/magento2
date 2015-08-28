<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Paypal\Controller\Adminhtml\Paypal\Reports;

use Magento\Framework\Controller\ResultFactory;

class Fetch extends \Magento\Paypal\Controller\Adminhtml\Paypal\Reports
{
    /**
     * Forced fetch reports action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        try {
            $reports = $this->_settlementFactory->create();
            /* @var $reports \Magento\Paypal\Model\Report\Settlement */
            $credentials = $reports->getSftpCredentials();
            if (empty($credentials)) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('We found nothing to fetch because of an empty configuration.')
                );
            }
            foreach ($credentials as $config) {
                try {
                    $fetched = $reports->fetchAndSave(
                        \Magento\Paypal\Model\Report\Settlement::createConnection($config)
                    );
                    $this->messageManager->addSuccess(
                        __('We fetched %1 report rows from "%2@%3."', $fetched, $config['username'], $config['hostname'])
                    );
                } catch (\Exception $e) {
                    $this->messageManager->addError(
                        __('We can\'t fetch reports from "%1@%2."', $config['username'], $config['hostname'])
                    );
                    $this->_logger->critical($e);
                }
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->_logger->critical($e);
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/index');
    }

    /**
     * {@inheritDoc}
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Paypal::fetch');
    }
}
