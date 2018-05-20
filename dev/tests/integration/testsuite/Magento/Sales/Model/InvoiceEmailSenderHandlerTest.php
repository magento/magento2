<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Model;

use Magento\Config\Model\Config;
use Magento\Store\Model\ScopeInterface;

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
     */
    public function testInvoiceEmailSenderExecute()
    {
        $expectedResult = 1;

        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        /** @var \Magento\Store\Model\Store $store */
        $store = $objectManager->create(\Magento\Store\Model\Store::class);
        $secondStoreCode = $store->load('fixture_second_store')->getCode();

        /** @var Config $storeConfig */
        $storeConfig = $objectManager->create(Config::class);
        $storeConfig->setScope(ScopeInterface::SCOPE_STORES);
        $storeConfig->setStore($secondStoreCode);
        $storeConfig->setDataByPath('sales_email/invoice/enabled', 0);
        $storeConfig->save();

        $beforeSendCollection = clone $this->entityCollection;
        $beforeSendCollection->addFieldToFilter('send_email', ['eq' => 1]);
        $beforeSendCollection->addFieldToFilter('email_sent', ['null' => true]);

        $this->emailSender->sendEmails();

        $this->entityCollection->addFieldToFilter('send_email', ['eq' => 1]);
        $this->entityCollection->addFieldToFilter('email_sent', ['null' => true]);

        $this->assertEquals($expectedResult, count($this->entityCollection->getItems()));
    }
}
