<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model;

use Exception;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\ValueFactory;
use Magento\Framework\App\Config\ValueInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Sales\Model\EmailSenderHandler;
use Magento\Sales\Model\Order\Email\Container\IdentityInterface;
use Magento\Sales\Model\Order\Email\Container\NullIdentity;
use Magento\Sales\Model\Order\Email\Sender;
use Magento\Sales\Model\ResourceModel\Collection\AbstractCollection;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\TestCase;

/**
 * Unit test of sales emails sending observer.
 */
class EmailSenderHandlerTest extends TestCase
{
    /** @var Sender $emailSender */
    private $emailSender;

    /** @var AbstractCollection $entityCollection */
    private $entityCollection;

    /** @var ScopeConfigInterface $globalConfig */
    private $globalConfig;

    /** @var DateTime|null $dateTime */
    private $dateTime;

    /** @var IdentityInterface|NullIdentity|null $identityContainer  */
    private $identityContainer;

    /** @var StoreManagerInterface|null $storeManager */
    private $storeManager;

    /** @var ValueFactory|null $configValueFactory */
    private $configValueFactory;

    /** @var string|null $modifyStartFromDate */
    private $modifyStartFromDate = '-1 day';

    /** @var EmailSenderHandler $testClass */
    private $testClass;

    /**
     * Setup method.
     *
     * @return void
     */
    public function setUp(): void
    {
        $this->emailSender = $this->getMockBuilder(Sender::class)
            ->addMethods(['send'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->entityCollection = $this->getMockBuilder(AbstractCollection::class)
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->getMock();

        $this->globalConfig = $this->getMockBuilder(ScopeConfigInterface::class)
            ->getMockForAbstractClass();

        $this->dateTime = $this->getMockBuilder(DateTime::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->identityContainer = $this->getMockBuilder(IdentityInterface::class)
            ->getMockForAbstractClass();

        $this->storeManager = $this->getMockBuilder(StoreManagerInterface::class)
            ->getMockForAbstractClass();

        $this->configValueFactory = $this->createMock(ValueFactory::class);

        $this->testClass = new EmailSenderHandler(
            $this->emailSender,
            $this->entityCollection,
            $this->globalConfig,
            $this->identityContainer,
            $this->storeManager,
            $this->configValueFactory,
            $this->modifyStartFromDate,
            $this->dateTime
        );
    }

    /**
     * Async email config option IS NOT enabled.
     *
     * @return void
     * @throws Exception
     */
    public function testAsyncEmailSendingIsNotEnabled(): void
    {
        $this->globalConfig->expects($this->once())
            ->method('isSetFlag')
            ->with('sales_email/general/async_sending')
            ->willReturn(false);

        $this->testClass->sendEmails();
    }

    /**
     * Test Async email sending.
     *
     * @return void
     * @throws Exception
     */
    public function testAsyncEmailSending(): void
    {
        $this->buildEntityFiltersForEmailSender();
        $this->sendEmailByStoreScope();

        $this->testClass->sendEmails();
    }

    /**
     * Build entity filter for email sender.
     *
     * @return void
     */
    private function buildEntityFiltersForEmailSender(): void
    {
        $this->globalConfig->expects($this->once())
            ->method('isSetFlag')
            ->with('sales_email/general/async_sending')
            ->willReturn(true);

        $this->entityCollection->expects($this->at(0))
            ->method('addFieldToFilter')
            ->with('send_email', ['eq' => true]);

        $this->entityCollection->expects($this->at(1))
            ->method('addFieldToFilter')
            ->with('email_sent', ['null' => true]);

        $configValue = $this->getMockBuilder(ValueInterface::class)
            ->addMethods(['load', 'getId', 'getUpdatedAt'])
            ->getMockForAbstractClass();

        $this->configValueFactory->expects($this->once())
            ->method('create')
            ->willReturn($configValue);

        $configValue->expects($this->once())
            ->method('load')
            ->with('sales_email/general/async_sending', 'path');

        $configValue->expects($this->once())
            ->method('getId')
            ->willReturn(rand(1, PHP_INT_MAX));

        $updatedAt = date('Y-m-d H:i:s');
        $strToTime = strtotime($updatedAt . ' ' . $this->modifyStartFromDate);
        $startFromDate = date('Y-m-d H:i:s', $strToTime);

        $configValue->expects($this->once())
            ->method('getUpdatedAt')
            ->willReturn($updatedAt);

        $this->dateTime->expects($this->once())
            ->method('date')
            ->with('Y-m-d H:i:s', $strToTime)
            ->willReturn($startFromDate);

        $this->entityCollection->expects($this->at(2))
            ->method('addFieldToFilter')
            ->with('created_at', ['gteq' => $startFromDate]);

        $pageSize = rand(1, PHP_INT_MAX);

        $this->globalConfig->expects($this->once())
            ->method('getValue')
            ->with('sales_email/general/sending_limit')
            ->willReturn($pageSize);

        $this->entityCollection->expects($this->once())
            ->method('setPageSize')
            ->with($pageSize);
    }

    /**
     * Send email by store scope.
     *
     * @return void
     */
    private function sendEmailByStoreScope(): void
    {
        $store = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->storeManager->expects($this->once())
            ->method('getStores')
            ->willReturn([$store, $store]);

        $this->identityContainer->expects($this->exactly(2))
            ->method('setStore')
            ->with($store);

        $this->identityContainer->expects($this->exactly(2))
            ->method('isEnabled')
            ->willReturnOnConsecutiveCalls(false, true);

        $storeId = rand(1, PHP_INT_MAX);

        $store->expects($this->once())
            ->method('getId')
            ->willReturn($storeId);

        $this->entityCollection->expects($this->at(4))
            ->method('addFieldToFilter')
            ->with('store_id', $storeId);

        $collectionItem = $this->getMockBuilder(DataObject::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->entityCollection->expects($this->once())
            ->method('getItems')
            ->willReturn([$collectionItem]);

        $this->emailSender->expects($this->once())
            ->method('send')
            ->with($collectionItem, true);
    }
}
