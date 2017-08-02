<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Cron;

/**
 * Class \Magento\Paypal\Cron\FetchReports
 *
 * @since 2.0.0
 */
class FetchReports
{
    /**
     * @var \Magento\Paypal\Model\Report\SettlementFactory
     * @since 2.0.0
     */
    protected $_settlementFactory;

    /**
     * Constructor
     *
     * @param \Magento\Paypal\Model\Report\SettlementFactory $settlementFactory
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Paypal\Model\Report\SettlementFactory $settlementFactory
    ) {
        $this->_settlementFactory = $settlementFactory;
    }

    /**
     * Goes to reports.paypal.com and fetches Settlement reports.
     *
     * @return void
     * @throws \Exception
     * @since 2.0.0
     */
    public function execute()
    {
        /** @var \Magento\Paypal\Model\Report\Settlement $reports */
        $reports = $this->_settlementFactory->create();
        /* @var $reports \Magento\Paypal\Model\Report\Settlement */
        $credentials = $reports->getSftpCredentials(true);
        foreach ($credentials as $config) {
            $reports->fetchAndSave(\Magento\Paypal\Model\Report\Settlement::createConnection($config));
        }
    }
}
