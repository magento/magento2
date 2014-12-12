<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Paypal\Adminhtml\Paypal;

/**
 * @magentoAppArea adminhtml
 */
class ReportsTest extends \Magento\Backend\Utility\Controller
{
    /**
     * @magentoConfigFixture current_store paypal/fetch_reports/active 1
     * @magentoConfigFixture current_store paypal/fetch_reports/ftp_ip 127.0.0.1
     * @magentoConfigFixture current_store paypal/fetch_reports/ftp_path /tmp
     * @magentoConfigFixture current_store paypal/fetch_reports/ftp_login login
     * @magentoConfigFixture current_store paypal/fetch_reports/ftp_password password
     * @magentoConfigFixture current_store paypal/fetch_reports/ftp_sandbox 0
     * @magentoDbIsolation enabled
     */
    public function testFetchAction()
    {
        $this->dispatch('backend/paypal/paypal_reports/fetch');
        $this->assertSessionMessages(
            $this->equalTo(["We couldn't fetch reports from 'login@127.0.0.1'."]),
            \Magento\Framework\Message\MessageInterface::TYPE_ERROR
        );
    }
}
