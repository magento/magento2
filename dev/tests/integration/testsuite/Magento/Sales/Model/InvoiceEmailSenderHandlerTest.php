<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Model;

class InvoiceEmailSenderHandlerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Invoice\Collection
     */
    private $entityCollection;

    /**
     * @var \Magento\Sales\Model\EmailSenderHandler
     */
    private $emailSender;

    protected function setUp()
    {
        /** @var \Magento\Sales\Model\Order\Email\Container\InvoiceIdentity $invoiceIdentity */
        $invoiceIdentity = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Sales\Model\Order\Email\Container\InvoiceIdentity::class
        );
        /** @var \Magento\Sales\Model\Order\Email\Sender\InvoiceSender $invoiceSender */
        $invoiceSender = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create(
                \Magento\Sales\Model\Order\Email\Sender\InvoiceSender::class,
                [
                    'identityContainer' => $invoiceIdentity,
                ]
            );
        $entityResource = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create(\Magento\Sales\Model\ResourceModel\Order\Invoice::class);
        $this->entityCollection = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Sales\Model\ResourceModel\Order\Invoice\Collection::class
        );
        $this->emailSender = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Sales\Model\EmailSenderHandler::class,
            [
                'emailSender'       => $invoiceSender,
                'entityResource'    => $entityResource,
                'entityCollection'  => $this->entityCollection,
                'identityContainer' => $invoiceIdentity,
            ]
        );
    }

    /**
     * @magentoAppIsolation  enabled
     * @magentoDbIsolation   enabled
     * @magentoDataFixture   Magento/Sales/_files/invoice_list_different_stores.php
     * @magentoConfigFixture default/sales_email/general/async_sending 1
     * @magentoConfigFixture current_store sales_email/invoice/enabled 1
     */
    public function testInvoiceEmailSenderExecute()
    {
        $expectedResult = 1;

        /** @var \Magento\Store\Model\Store $store */
        $store = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create(\Magento\Store\Model\Store::class);
        $secondStoreId = $store->load('fixture_second_store')->getId();

        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Framework\App\Config\MutableScopeConfigInterface::class
        )->setValue(
            'sales_email/invoice/enabled',
            0,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $secondStoreId
        );

        $beforeSendCollection = clone $this->entityCollection;
        $beforeSendCollection->addFieldToFilter('send_email', ['eq' => 1]);
        $beforeSendCollection->addFieldToFilter('email_sent', ['null' => true]);

        $this->emailSender->sendEmails();

        $this->entityCollection->addFieldToFilter('send_email', ['eq' => 1]);
        $this->entityCollection->addFieldToFilter('email_sent', ['null' => true]);

        $this->assertEquals($expectedResult, count($this->entityCollection->getItems()));
    }
}
