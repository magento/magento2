<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Block\Adminhtml\Edit\Tab;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Model\Session;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Block\Adminhtml\Edit\Tab\Newsletter;
use Magento\Customer\Controller\RegistryConstants;
use Magento\Customer\Model\Config\Share;
use Magento\Framework\Data\Form;
use Magento\Framework\Data\Form\Element\Checkbox;
use Magento\Framework\Data\Form\Element\Fieldset;
use Magento\Framework\Data\Form\Element\Select;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\UrlInterface;
use Magento\Newsletter\Model\Subscriber;
use Magento\Newsletter\Model\SubscriberFactory;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\System\Store as SystemStore;
use Magento\Store\Model\Website;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test Customer account form block
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class NewsletterTest extends TestCase
{
    /**
     * @var Newsletter
     */
    private $model;

    /**
     * @var Context|MockObject
     */
    private $contextMock;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManager;

    /**
     * @var Registry|MockObject
     */
    private $registryMock;

    /**
     * @var FormFactory|MockObject
     */
    private $formFactoryMock;

    /**
     * @var SubscriberFactory|MockObject
     */
    private $subscriberFactoryMock;

    /**
     * @var AccountManagementInterface|MockObject
     */
    private $accountManagementMock;

    /**
     * @var UrlInterface|MockObject
     */
    private $urlBuilderMock;

    /**
     * @var Session|MockObject
     */
    private $backendSessionMock;

    /**
     * @var SystemStore|MockObject
     */
    private $systemStore;

    /**
     * @var CustomerRepositoryInterface|MockObject
     */
    private $customerRepository;

    /**
     * @var Share|MockObject
     */
    private $shareConfig;

    /** @var TimezoneInterface|MockObject */
    protected $localeDateMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->contextMock = $this->createMock(Context::class);
        $this->localeDateMock = $this->getMockBuilder(TimezoneInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['formatDateTime'])
            ->getMockForAbstractClass();
        $this->contextMock->expects($this->any())->method('getLocaleDate')->willReturn($this->localeDateMock);
        $this->registryMock = $this->createMock(Registry::class);
        $this->formFactoryMock = $this->createMock(FormFactory::class);
        $this->subscriberFactoryMock = $this->createPartialMock(
            SubscriberFactory::class,
            ['create']
        );
        $this->accountManagementMock = $this->getMockForAbstractClass(AccountManagementInterface::class);
        $this->urlBuilderMock = $this->getMockForAbstractClass(UrlInterface::class);
        $this->storeManager = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $this->backendSessionMock = $this->getMockBuilder(Session::class)
            ->addMethods(['getCustomerFormData'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->contextMock->expects($this->once())
            ->method('getUrlBuilder')
            ->willReturn($this->urlBuilderMock);
        $this->contextMock->expects($this->once())
            ->method('getBackendSession')
            ->willReturn($this->backendSessionMock);
        $this->contextMock->method('getStoreManager')
            ->willReturn($this->storeManager);
        $this->systemStore = $this->createMock(SystemStore::class);
        $this->customerRepository = $this->getMockForAbstractClass(CustomerRepositoryInterface::class);
        $this->shareConfig = $this->createMock(Share::class);

        $objectManager = new ObjectManager($this);
        $objectManager->prepareObjectManager();
        $this->model = $objectManager->getObject(
            Newsletter::class,
            [
                'context' => $this->contextMock,
                'registry' => $this->registryMock,
                'formFactory' => $this->formFactoryMock,
                'subscriberFactory' => $this->subscriberFactoryMock,
                'customerAccountManagement' => $this->accountManagementMock,
                'systemStore' => $this->systemStore,
                'customerRepository' => $this->customerRepository,
                'shareConfig' => $this->shareConfig,
            ]
        );
    }

    /**
     * Test to initialize the form without current customer
     */
    public function testInitFormCanNotShowTab()
    {
        $this->registryMock->expects($this->once())
            ->method('registry')
            ->with(RegistryConstants::CURRENT_CUSTOMER_ID)
            ->willReturn(false);

        $this->assertSame($this->model, $this->model->initForm());
    }

    /**
     * Test getSubscriberStatusChangedDate
     *
     * @dataProvider getChangeStatusAtDataProvider
     */
    public function testGetSubscriberStatusChangedDate($statusDate, $dateExpected)
    {
        $customerId = 999;
        $websiteId = 1;
        $storeId = 1;
        $isSubscribed = true;

        $this->registryMock->method('registry')->with(RegistryConstants::CURRENT_CUSTOMER_ID)
            ->willReturn($customerId);

        $customer = $this->getMockForAbstractClass(CustomerInterface::class);
        $customer->method('getWebsiteId')->willReturn($websiteId);
        $customer->method('getStoreId')->willReturn($storeId);
        $customer->method('getId')->willReturn($customerId);
        $this->customerRepository->method('getById')->with($customerId)->willReturn($customer);

        $subscriberMock = $this->getMockBuilder(Subscriber::class)
            ->disableOriginalConstructor()
            ->addMethods(['getChangeStatusAt'])
            ->onlyMethods(['loadByCustomer', 'isSubscribed', 'getData'])
            ->getMock();
        $statusDate = new \DateTime($statusDate);
        $this->localeDateMock->method('formatDateTime')->with($statusDate)->willReturn($dateExpected);

        $subscriberMock->method('loadByCustomer')->with($customerId, $websiteId)->willReturnSelf();
        $subscriberMock->method('getChangeStatusAt')->willReturn($statusDate);
        $subscriberMock->method('isSubscribed')->willReturn($isSubscribed);
        $subscriberMock->method('getData')->willReturn([]);
        $this->subscriberFactoryMock->expects($this->any())->method('create')->willReturn($subscriberMock);
        $this->assertEquals($dateExpected, $this->model->getStatusChangedDate());
    }

    /**
     * Data provider for testGetSubscriberStatusChangedDate
     *
     * @return array
     */
    public static function getChangeStatusAtDataProvider()
    {
        return
            [
                ['',''],
                ['Nov 22, 2023, 1:00:00 AM','Nov 23, 2023, 2:00:00 AM']
            ];
    }

    /**
     * Test to initialize the form
     */
    public function testInitForm()
    {
        $customerId = 1;
        $websiteId = 1;
        $storeId = 2;
        $websiteName = 'Website Name';
        $isSubscribed = true;

        $this->registryMock->method('registry')->with(RegistryConstants::CURRENT_CUSTOMER_ID)
            ->willReturn($customerId);

        $customer = $this->getMockForAbstractClass(CustomerInterface::class);
        $customer->method('getWebsiteId')->willReturn($websiteId);
        $customer->method('getStoreId')->willReturn($storeId);
        $customer->method('getId')->willReturn($customerId);
        $this->customerRepository->method('getById')->with($customerId)->willReturn($customer);
        $subscriberMock = $this->createMock(Subscriber::class);
        $subscriberMock->method('loadByCustomer')->with($customerId, $websiteId)->willReturnSelf();
        $subscriberMock->method('isSubscribed')->willReturn($isSubscribed);
        $subscriberMock->method('getData')->willReturn([]);
        $this->subscriberFactoryMock->expects($this->once())->method('create')->willReturn($subscriberMock);

        $website = $this->createMock(Website::class);
        $website->method('getStoresCount')->willReturn(1);
        $website->method('getId')->willReturn($websiteId);
        $store = $this->createMock(Store::class);
        $store->method('getWebsiteId')->willReturn($websiteId);
        $this->storeManager->method('getStore')->with($storeId)->willReturn($store);
        $this->storeManager->method('getWebsites')->willReturn([$website]);
        $this->storeManager->method('isSingleStoreMode')->willReturn(true);
        $this->systemStore->method('getStoreOptionsTree')->willReturn([]);
        $this->systemStore->method('getWebsiteName')->with($websiteId)->willReturn($websiteName);

        $statusElementMock = $this->createMock(Checkbox::class);
        $statusElementMock->expects($this->once())
            ->method('setIsChecked')
            ->with($isSubscribed);
        $fieldsetMock = $this->createMock(Fieldset::class);
        $fieldsetMock->expects($this->once())
            ->method('addField')
            ->with(
                'subscription_status_' . $websiteId,
                'checkbox',
                [
                    'label' => __('Subscribed to Newsletter'),
                    'name' => "subscription_status[$websiteId]",
                    'data-form-part' => null,
                    'value' => $isSubscribed,
                    'onchange' => 'this.value = this.checked;'
                ]
            )
            ->willReturn($statusElementMock);
        $fieldsetMock->expects($this->once())->method('setReadonly')->with(true, true);
        $formMock = $this->getMockBuilder(Form::class)
            ->addMethods(['setHtmlIdPrefix', 'setForm', 'setParent', 'setBaseUrl'])
            ->onlyMethods(['addFieldset', 'setValues', 'getElement'])
            ->disableOriginalConstructor()
            ->getMock();
        $formMock->expects($this->once())->method('setHtmlIdPrefix')->with('_newsletter');
        $formMock->expects($this->once())->method('addFieldset')->willReturn($fieldsetMock);
        $this->formFactoryMock->expects($this->once())->method('create')->willReturn($formMock);
        $this->accountManagementMock->expects($this->once())
            ->method('isReadOnly')
            ->with($customerId)
            ->willReturn(true);
        $this->backendSessionMock->expects($this->once())
            ->method('getCustomerFormData')
            ->willReturn(null);

        $this->assertSame($this->model, $this->model->initForm());
    }

    /**
     * Test to initialize the form with session form data
     */
    public function testInitFormWithCustomerFormData()
    {
        $customerId = 1;
        $websiteId = 1;
        $storeId = 2;
        $websiteName = 'Website Name';
        $isSubscribed = true;
        $isSubscribedCustomerSession = false;

        $this->registryMock->method('registry')->with(RegistryConstants::CURRENT_CUSTOMER_ID)
            ->willReturn($customerId);
        $customer = $this->getMockForAbstractClass(CustomerInterface::class);
        $customer->method('getWebsiteId')->willReturn($websiteId);
        $customer->method('getStoreId')->willReturn($storeId);
        $customer->method('getId')->willReturn($customerId);
        $this->customerRepository->method('getById')->with($customerId)->willReturn($customer);
        $subscriberMock = $this->createMock(Subscriber::class);
        $subscriberMock->method('loadByCustomer')->with($customerId, $websiteId)->willReturnSelf();
        $subscriberMock->method('isSubscribed')->willReturn($isSubscribed);
        $subscriberMock->method('getData')->willReturn([]);
        $this->subscriberFactoryMock->expects($this->once())->method('create')->willReturn($subscriberMock);
        $website = $this->createMock(Website::class);
        $website->method('getStoresCount')->willReturn(1);
        $website->method('getId')->willReturn($websiteId);
        $store = $this->createMock(Store::class);
        $store->method('getWebsiteId')->willReturn($websiteId);
        $this->storeManager->method('getStore')->with($storeId)->willReturn($store);
        $this->storeManager->method('getWebsites')->willReturn([$website]);
        $this->storeManager->method('isSingleStoreMode')->willReturn(true);
        $this->systemStore->method('getStoreOptionsTree')->willReturn([]);
        $this->systemStore->method('getWebsiteName')->with($websiteId)->willReturn($websiteName);
        $statusElementMock = $this->createMock(Checkbox::class);
        $statusElementMock->expects($this->once())
            ->method('setIsChecked')
            ->with($isSubscribed);
        $fieldsetMock = $this->createMock(Fieldset::class);
        $fieldsetMock->expects($this->once())
            ->method('addField')
            ->with(
                'subscription_status_' . $websiteId,
                'checkbox',
                [
                    'label' => __('Subscribed to Newsletter'),
                    'name' => "subscription_status[$websiteId]",
                    'data-form-part' => null,
                    'value' => $isSubscribed,
                    'onchange' => 'this.value = this.checked;'
                ]
            )
            ->willReturn($statusElementMock);
        $fieldsetMock->expects($this->once())->method('setReadonly')->with(true, true);
        $statusElementForm = $this->getMockBuilder(Checkbox::class)
            ->addMethods(['setChecked', 'setValue'])
            ->disableOriginalConstructor()
            ->getMock();
        $statusElementForm->method('setValue')
            ->with($isSubscribedCustomerSession);
        $statusElementForm->method('setChecked')
            ->with($isSubscribedCustomerSession);
        $storeElementForm = $this->getMockBuilder(Select::class)
            ->addMethods(['setValue'])
            ->disableOriginalConstructor()
            ->getMock();
        $storeElementForm->method('setValue')
            ->with(Store::DEFAULT_STORE_ID);
        $formMock = $this->getMockBuilder(Form::class)
            ->addMethods(['setHtmlIdPrefix', 'setForm', 'setParent', 'setBaseUrl'])
            ->onlyMethods(['addFieldset', 'setValues', 'getElement'])
            ->disableOriginalConstructor()
            ->getMock();
        $formMock->expects($this->once())->method('setHtmlIdPrefix')->with('_newsletter');
        $formMock->expects($this->once())->method('addFieldset')->willReturn($fieldsetMock);
        $formMock->method('getElement')
            ->willReturnMap(
                [
                    ['subscription_status_' . $websiteId, $statusElementForm],
                    ['subscription_store_' . $websiteId, $storeElementForm],
                ]
            );
        $this->formFactoryMock->expects($this->once())->method('create')->willReturn($formMock);
        $this->accountManagementMock->expects($this->once())
            ->method('isReadOnly')
            ->with($customerId)
            ->willReturn(true);
        $this->backendSessionMock->expects($this->once())
            ->method('getCustomerFormData')
            ->willReturn(
                [
                    'customer' => ['entity_id' => $customerId],
                    'subscription_status' => [$websiteId => $isSubscribedCustomerSession]
                ]
            );

        $this->assertSame($this->model, $this->model->initForm());
    }
}
