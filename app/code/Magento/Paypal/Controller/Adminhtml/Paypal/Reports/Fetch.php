<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Paypal\Controller\Adminhtml\Paypal\Reports;

class Fetch extends \Magento\Paypal\Controller\Adminhtml\Paypal\Reports
{
    /**
     * Forced fetch reports action
     *
     * @return void
     * @throws \Magento\Framework\Model\Exception
     */
    public function execute()
    {
        try {
            $reports = $this->_settlementFactory->create();
            /* @var $reports \Magento\Paypal\Model\Report\Settlement */
            $credentials = $reports->getSftpCredentials();
            if (empty($credentials)) {
                throw new \Magento\Framework\Model\Exception(__('We found nothing to fetch because of an empty configuration.'));
            }
            foreach ($credentials as $config) {
                try {
                    $fetched = $reports->fetchAndSave(
                        \Magento\Paypal\Model\Report\Settlement::createConnection($config)
                    );
                    $this->messageManager->addSuccess(
                        __(
                            "We fetched %1 report rows from '%2@%3'.",
                            $fetched,
                            $config['username'],
                            $config['hostname']
                        )
                    );
                } catch (\Exception $e) {
                    $this->messageManager->addError(
                        __("We couldn't fetch reports from '%1@%2'.", $config['username'], $config['hostname'])
                    );
                    $this->_logger->logException($e);
                }
            }
        } catch (\Magento\Framework\Model\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->_logger->logException($e);
        }
        $this->_redirect('*/*/index');
    }
}
