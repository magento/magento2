<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\ProductAlert\Test\Unit\Model;

use Magento\Catalog\Model\Product;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Helper\View;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\State;
use Magento\Framework\Event\ManagerInterface as EventManagerInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Mail\TransportInterface;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\ProductAlert\Block\Email\Price;
use Magento\ProductAlert\Block\Email\Stock;
use Magento\ProductAlert\Helper\Data;
use Magento\ProductAlert\Model\Email;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\App\Emulation;
use Magento\Store\Model\Group;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\Website;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for the ProductAlert Email model.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class EmailTest extends TestCase
{
    /**
     * @var Email
     */
    private $model;

    /**
     * @var Data|MockObject
     */
    private $productAlertDataMock;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $scopeConfigMock;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManagerMock;

    /**
     * @var CustomerRepositoryInterface|MockObject
     */
    private $customerRepositoryMock;

    /**
     * @var View|MockObject
     */
    private $customerHelperMock;

    /**
     * @var Emulation|MockObject
     */
    private $appEmulationMock;

    /**
     * @var TransportBuilder|MockObject
     */
    private $transportBuilderMock;

    /**
     * @var State|MockObject
     */
    private $appStateMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->appStateMock = $this->createMock(State::class);
        $eventManagerMock = $this->createMock(EventManagerInterface::class);

        $contextMock = $this->createMock(Context::class);
        $contextMock->method('getAppState')->willReturn($this->appStateMock);
        $contextMock->method('getEventDispatcher')->willReturn($eventManagerMock);

        $this->productAlertDataMock = $this->createMock(Data::class);
        $this->scopeConfigMock = $this->createMock(ScopeConfigInterface::class);
        $this->storeManagerMock = $this->createMock(StoreManagerInterface::class);
        $this->customerRepositoryMock = $this->createMock(CustomerRepositoryInterface::class);
        $this->customerHelperMock = $this->createMock(View::class);
        $this->appEmulationMock = $this->createMock(Emulation::class);
        $this->transportBuilderMock = $this->createMock(TransportBuilder::class);

        $this->model = new Email(
            $contextMock,
            $this->createMock(Registry::class),
            $this->productAlertDataMock,
            $this->scopeConfigMock,
            $this->storeManagerMock,
            $this->customerRepositoryMock,
            $this->customerHelperMock,
            $this->appEmulationMock,
            $this->transportBuilderMock
        );
    }

    /**
     * Type defaults to "price" and can be changed.
     *
     * @covers \Magento\ProductAlert\Model\Email::getType()
     * @covers \Magento\ProductAlert\Model\Email::setType()
     * @return void
     */
    public function testGetSetType(): void
    {
        $this->assertSame('price', $this->model->getType());
        $this->model->setType('stock');
        $this->assertSame('stock', $this->model->getType());
    }

    /**
     * setWebsite() is fluent.
     *
     * @covers \Magento\ProductAlert\Model\Email::setWebsite()
     * @return void
     */
    public function testSetWebsite(): void
    {
        $websiteMock = $this->createMock(Website::class);
        $this->assertSame($this->model, $this->model->setWebsite($websiteMock));
    }

    /**
     * setWebsiteId() resolves the website through the store manager and is fluent.
     *
     * @covers \Magento\ProductAlert\Model\Email::setWebsiteId()
     * @return void
     */
    public function testSetWebsiteId(): void
    {
        $websiteId = 5;
        $websiteMock = $this->createMock(Website::class);
        $this->storeManagerMock->expects($this->once())
            ->method('getWebsite')
            ->with($websiteId)
            ->willReturn($websiteMock);

        $this->assertSame($this->model, $this->model->setWebsiteId($websiteId));
    }

    /**
     * setCustomerId() loads the customer through the repository and is fluent.
     *
     * @covers \Magento\ProductAlert\Model\Email::setCustomerId()
     * @return void
     */
    public function testSetCustomerId(): void
    {
        $customerId = 42;
        $customerMock = $this->createMock(CustomerInterface::class);
        $this->customerRepositoryMock->expects($this->once())
            ->method('getById')
            ->with($customerId)
            ->willReturn($customerMock);

        $this->assertSame($this->model, $this->model->setCustomerId($customerId));
    }

    /**
     * setCustomerData() is fluent.
     *
     * @covers \Magento\ProductAlert\Model\Email::setCustomerData()
     * @return void
     */
    public function testSetCustomerData(): void
    {
        $customerMock = $this->createMock(CustomerInterface::class);
        $this->assertSame($this->model, $this->model->setCustomerData($customerMock));
    }

    /**
     * clean(), addPriceProduct() and addStockProduct() are all fluent.
     *
     * @covers \Magento\ProductAlert\Model\Email::addPriceProduct()
     * @covers \Magento\ProductAlert\Model\Email::addStockProduct()
     * @covers \Magento\ProductAlert\Model\Email::clean()
     * @return void
     */
    public function testFluentProductAndCleanMethods(): void
    {
        $productMock = $this->createMock(Product::class);
        $productMock->method('getId')->willReturn(1);

        $this->assertSame($this->model, $this->model->addPriceProduct($productMock));
        $this->assertSame($this->model, $this->model->addStockProduct($productMock));
        $this->assertSame($this->model, $this->model->clean());
    }

    /**
     * send() returns false when no website has been set.
     *
     * @covers \Magento\ProductAlert\Model\Email::send()
     * @return void
     */
    public function testSendReturnsFalseWithoutWebsite(): void
    {
        $this->model->setCustomerData($this->createMock(CustomerInterface::class));
        $this->assertFalse($this->model->send());
    }

    /**
     * send() returns false when no customer has been set.
     *
     * @covers \Magento\ProductAlert\Model\Email::send()
     * @return void
     */
    public function testSendReturnsFalseWithoutCustomer(): void
    {
        $this->model->setWebsite($this->createWebsiteMock(true));
        $this->assertFalse($this->model->send());
    }

    /**
     * send() returns false when the website has no default store.
     *
     * @covers \Magento\ProductAlert\Model\Email::send()
     * @return void
     */
    public function testSendReturnsFalseWithoutDefaultStore(): void
    {
        $this->model->setWebsite($this->createWebsiteMock(false));
        $this->model->setCustomerData($this->createMock(CustomerInterface::class));
        $this->assertFalse($this->model->send());
    }

    /**
     * send() returns false when the type is not supported.
     *
     * @covers \Magento\ProductAlert\Model\Email::send()
     * @return void
     */
    public function testSendReturnsFalseForUnsupportedType(): void
    {
        $this->model->setWebsite($this->createWebsiteMock(true));
        $this->model->setCustomerData($this->createMock(CustomerInterface::class));
        $this->model->setType('unsupported');

        $this->assertFalse($this->model->send());
    }

    /**
     * send() returns false when there are no products to notify about.
     *
     * @covers \Magento\ProductAlert\Model\Email::send()
     * @return void
     */
    public function testSendReturnsFalseWithoutProducts(): void
    {
        $this->model->setWebsite($this->createWebsiteMock(true));
        $this->model->setCustomerData($this->createMock(CustomerInterface::class));

        $this->assertFalse($this->model->send());
    }

    /**
     * send() builds and dispatches a price alert email for the collected products.
     *
     * @covers \Magento\ProductAlert\Model\Email::send()
     * @return void
     */
    public function testSendPriceAlertSucceeds(): void
    {
        $storeId = 3;
        $templateId = 'catalog_productalert_email_price_template';
        $emailIdentity = 'general';
        $alertGrid = '<table>price</table>';
        $customerName = 'John Doe';
        $groupId = 7;

        $customerMock = $this->createMock(CustomerInterface::class);
        $customerMock->method('getStoreId')->willReturn($storeId);
        $customerMock->method('getGroupId')->willReturn($groupId);
        $customerMock->method('getEmail')->willReturn('john@example.com');

        // setCustomerGroupId() is a magic setter (AbstractModel::__call) and cannot be
        // configured on the mock; the call inside send() passes through __call harmlessly.
        $productMock = $this->createMock(Product::class);
        $productMock->method('getId')->willReturn(11);

        $storeMock = $this->createMock(StoreInterface::class);
        $this->storeManagerMock->expects($this->once())
            ->method('getStore')
            ->with($storeId)
            ->willReturn($storeMock);

        $blockMock = $this->createMock(Price::class);
        $blockMock->method('setStore')->with($storeMock)->willReturnSelf();
        $blockMock->expects($this->once())->method('reset')->willReturnSelf();
        $blockMock->expects($this->once())->method('addProduct')->with($productMock);
        $this->productAlertDataMock->expects($this->once())
            ->method('createBlock')
            ->with(Price::class)
            ->willReturn($blockMock);

        // Emulation start/stop call count is an implementation detail; assert only that it runs.
        $this->appEmulationMock->expects($this->atLeastOnce())->method('startEnvironmentEmulation');
        $this->appEmulationMock->expects($this->atLeastOnce())->method('stopEnvironmentEmulation');
        $this->appStateMock->method('emulateAreaCode')->willReturn($alertGrid);

        $this->scopeConfigMock->method('getValue')
            ->willReturnMap(
                [
                    [
                        Email::XML_PATH_EMAIL_PRICE_TEMPLATE,
                        ScopeInterface::SCOPE_STORE,
                        $storeId,
                        $templateId,
                    ],
                    [
                        Email::XML_PATH_EMAIL_IDENTITY,
                        ScopeInterface::SCOPE_STORE,
                        $storeId,
                        $emailIdentity,
                    ],
                ]
            );

        $this->customerHelperMock->method('getCustomerName')->with($customerMock)->willReturn($customerName);

        $this->configureTransportBuilder($templateId, $storeId, $customerName, $alertGrid, $emailIdentity);

        $this->model->setWebsite($this->createWebsiteMock(true));
        $this->model->setCustomerData($customerMock);
        $this->model->addPriceProduct($productMock);

        $this->assertTrue($this->model->send());
    }

    /**
     * send() builds and dispatches a stock alert email using the stock block.
     *
     * @covers \Magento\ProductAlert\Model\Email::send()
     * @return void
     */
    public function testSendStockAlertSucceeds(): void
    {
        $storeId = 1;
        $templateId = 'catalog_productalert_email_stock_template';
        $emailIdentity = 'general';
        $alertGrid = '<table>stock</table>';
        $customerName = 'Jane Roe';
        $groupId = 2;

        $customerMock = $this->createMock(CustomerInterface::class);
        $customerMock->method('getStoreId')->willReturn($storeId);
        $customerMock->method('getGroupId')->willReturn($groupId);
        $customerMock->method('getEmail')->willReturn('jane@example.com');

        // setCustomerGroupId() is a magic setter (AbstractModel::__call) and cannot be
        // configured on the mock; the call inside send() passes through __call harmlessly.
        $productMock = $this->createMock(Product::class);
        $productMock->method('getId')->willReturn(22);

        $storeMock = $this->createMock(StoreInterface::class);
        $this->storeManagerMock->method('getStore')->with($storeId)->willReturn($storeMock);

        $blockMock = $this->createMock(Stock::class);
        $blockMock->method('setStore')->with($storeMock)->willReturnSelf();
        $blockMock->expects($this->once())->method('reset')->willReturnSelf();
        $blockMock->expects($this->once())->method('addProduct')->with($productMock);
        $this->productAlertDataMock->expects($this->once())
            ->method('createBlock')
            ->with(Stock::class)
            ->willReturn($blockMock);

        // Emulation start/stop call count is an implementation detail; assert only that it runs.
        $this->appEmulationMock->expects($this->atLeastOnce())->method('startEnvironmentEmulation');
        $this->appEmulationMock->expects($this->atLeastOnce())->method('stopEnvironmentEmulation');
        $this->appStateMock->method('emulateAreaCode')->willReturn($alertGrid);

        $this->scopeConfigMock->method('getValue')
            ->willReturnMap(
                [
                    [
                        Email::XML_PATH_EMAIL_STOCK_TEMPLATE,
                        ScopeInterface::SCOPE_STORE,
                        $storeId,
                        $templateId,
                    ],
                    [
                        Email::XML_PATH_EMAIL_IDENTITY,
                        ScopeInterface::SCOPE_STORE,
                        $storeId,
                        $emailIdentity,
                    ],
                ]
            );

        $this->customerHelperMock->method('getCustomerName')->with($customerMock)->willReturn($customerName);

        $this->configureTransportBuilder($templateId, $storeId, $customerName, $alertGrid, $emailIdentity);

        $this->model->setType('stock');
        $this->model->setWebsite($this->createWebsiteMock(true));
        $this->model->setCustomerData($customerMock);
        $this->model->addStockProduct($productMock);

        $this->assertTrue($this->model->send());
    }

    /**
     * Build a website mock, optionally with a default store configured.
     *
     * @param bool $withDefaultStore
     * @return Website|MockObject
     */
    private function createWebsiteMock(bool $withDefaultStore): Website
    {
        $groupMock = $this->createMock(Group::class);
        if ($withDefaultStore) {
            $groupMock->method('getDefaultStore')->willReturn($this->createMock(StoreInterface::class));
        } else {
            $groupMock->method('getDefaultStore')->willReturn(null);
        }

        $websiteMock = $this->createMock(Website::class);
        $websiteMock->method('getDefaultGroup')->willReturn($groupMock);

        return $websiteMock;
    }

    /**
     * Configure the transport builder mock fluent chain for a successful send.
     *
     * @param string $templateId
     * @param int $storeId
     * @param string $customerName
     * @param string $alertGrid
     * @param string $emailIdentity
     * @return void
     */
    private function configureTransportBuilder(
        string $templateId,
        int $storeId,
        string $customerName,
        string $alertGrid,
        string $emailIdentity
    ): void {
        $this->transportBuilderMock->expects($this->once())
            ->method('setTemplateIdentifier')
            ->with($templateId)
            ->willReturnSelf();
        $this->transportBuilderMock->expects($this->once())
            ->method('setTemplateOptions')
            ->willReturnSelf();
        $this->transportBuilderMock->expects($this->once())
            ->method('setTemplateVars')
            ->with(['customerName' => $customerName, 'alertGrid' => $alertGrid])
            ->willReturnSelf();
        $this->transportBuilderMock->expects($this->once())
            ->method('setFromByScope')
            ->with($emailIdentity, $storeId)
            ->willReturnSelf();
        $this->transportBuilderMock->expects($this->once())
            ->method('addTo')
            ->willReturnSelf();

        $transportMock = $this->createMock(TransportInterface::class);
        $transportMock->expects($this->once())->method('sendMessage');
        $this->transportBuilderMock->expects($this->once())->method('getTransport')->willReturn($transportMock);
    }
}
