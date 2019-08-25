<?php
declare(strict_types=1);

namespace Magento\NewRelicReporting\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * @TODO Remove with all the uses in 2.4-dev branch
 * @package Magento\NewRelicReporting\Observer
 */
class NewrelicEndTransactionObserver implements ObserverInterface
{
    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $this->endTransaction(true);
    }

    /**
     * @param bool $ignore
     * @return void
     */
    private function endTransaction($ignore = false)
    {
        if ($this->isExtensionInstalled()) {
            newrelic_end_transaction($ignore);
        }
    }

    /**
     * Checks whether newrelic-php5 agent is installed
     *
     * @return bool
     */
    public function isExtensionInstalled()
    {
        if (extension_loaded('newrelic')) {
            return true;
        }
        return false;
    }
}
