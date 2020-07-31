<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Observer;

use Magento\Customer\Api\Data\GroupInterface;
use Magento\Customer\Api\GroupManagementInterface;
use Magento\Customer\Helper\Address as HelperAddress;
use Magento\Customer\Model\Address\AbstractAddress;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\Vat;
use Magento\Customer\Observer\AfterAddressSaveObserver;
use Magento\Customer\Observer\BeforeAddressSaveObserver;
use Magento\Framework\App\Area;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\State as AppState;
use Magento\Framework\DataObject;
use Magento\Framework\Escaper;
use Magento\Framework\Event\Observer;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Registry;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AfterAddressSaveObserverTest extends TestCase
{
    /**
     * @var AfterAddressSaveObserver
     */
    protected $model;

    /**
     * @var Vat|MockObject
     */
    protected $vat;

    /**
     * @var HelperAddress|MockObject
     */
    protected $helperAddress;

    /**
     * @var Registry|MockObject
     */
    protected $registry;

    /**
     * @var GroupManagementInterface|MockObject
     */
    protected $groupManagement;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    protected $scopeConfig;

    /**
     * @var ManagerInterface|MockObject
     */
    protected $messageManager;

    /**
     * @var Escaper|MockObject
     */
    protected $escaper;

    /**
     * @var AppState|MockObject
     */
    protected $appState;

    /**
     * @var Session|MockObject
     */
    protected $customerSessionMock;

    /**
     * @var GroupInterface|MockObject
     */
    protected $group;

    protected function setUp(): void
    {
        $this->vat = $this->createMock(Vat::class);
        $this->helperAddress = $this->createMock(HelperAddress::class);
        $this->registry = $this->createMock(Registry::class);
        $this->escaper = $this->createMock(Escaper::class);
        $this->appState = $this->createMock(AppState::class);
        $this->customerSessionMock = $this->createMock(Session::class);
        $this->group = $this->getMockBuilder(GroupInterface::class)
            ->setMethods(['getId'])
            ->getMockForAbstractClass();
        $this->group->expects($this->any())->method('getId')->willReturn(1);
        $this->groupManagement = $this->getMockBuilder(GroupManagementInterface::class)
            ->setMethods(['getDefaultGroup'])
            ->getMockForAbstractClass();
        $this->groupManagement->expects($this->any())->method('getDefaultGroup')->willReturn($this->group);

        $this->scopeConfig = $this->getMockBuilder(ScopeConfigInterface::class)
            ->getMockForAbstractClass();
        $this->messageManager = $this->getMockBuilder(ManagerInterface::class)
            ->getMockForAbstractClass();

        $this->model = new AfterAddressSaveObserver(
            $this->vat,
            $this->helperAddress,
            $this->registry,
            $this->groupManagement,
            $this->scopeConfig,
            $this->messageManager,
            $this->escaper,
            $this->appState,
            $this->customerSessionMock
        );
    }

    /**
     * @param bool $isVatValidationEnabled
     * @param bool $processedFlag
     * @param bool $forceProcess
     * @param int $addressId
     * @param int $registeredAddressId
     * @param string $configAddressType
     * @dataProvider dataProviderAfterAddressSaveRestricted
     */
    public function testAfterAddressSaveRestricted(
        $isVatValidationEnabled,
        $processedFlag,
        $forceProcess,
        $addressId,
        $registeredAddressId,
        $configAddressType
    ) {
        $store = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->getMock();

        $customer = $this->getMockBuilder(Customer::class)
            ->disableOriginalConstructor()
            ->setMethods(['getDefaultBilling', 'getStore', 'getDefaultShipping', 'getGroupId'])
            ->getMock();
        $customer->expects($this->any())
            ->method('getStore')
            ->willReturn($store);
        $customer->expects($this->any())
            ->method('getDefaultBilling')
            ->willReturn(null);
        $customer->expects($this->any())
            ->method('getDefaultShipping')
            ->willReturn(null);
        $customer->expects($this->any())
            ->method('getGroupID')
            ->willReturn(1);

        $address = $this->getMockBuilder(\Magento\Customer\Model\Address::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'getId',
                    'getIsDefaultBilling',
                    'getIsDefaultShipping',
                    'setForceProcess',
                    'getIsPrimaryBilling',
                    'getIsPrimaryShipping',
                    'getCustomer',
                    'getForceProcess'
                ]
            )
            ->getMock();
        $address->expects($this->any())
            ->method('getId')
            ->willReturn($addressId);
        $address->expects($this->any())
            ->method('getCustomer')
            ->willReturn($customer);
        $address->expects($this->any())
            ->method('getForceProcess')
            ->willReturn($forceProcess);
        $address->expects($this->any())
            ->method('getIsPrimaryBilling')
            ->willReturn(null);
        $address->expects($this->any())
            ->method('getIsDefaultBilling')
            ->willReturn($addressId);
        $address->expects($this->any())
            ->method('getIsPrimaryShipping')
            ->willReturn(null);
        $address->expects($this->any())
            ->method('getIsDefaultShipping')
            ->willReturn($addressId);

        $observer = $this->getMockBuilder(Observer::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getCustomerAddress',
            ])
            ->getMock();
        $observer->expects($this->once())
            ->method('getCustomerAddress')
            ->willReturn($address);

        $this->helperAddress->expects($this->once())
            ->method('isVatValidationEnabled')
            ->with($store)
            ->willReturn($isVatValidationEnabled);

        $this->registry->expects($this->any())
            ->method('registry')
            ->willReturnMap([
                [AfterAddressSaveObserver::VIV_PROCESSED_FLAG, $processedFlag],
                [BeforeAddressSaveObserver::VIV_CURRENTLY_SAVED_ADDRESS, $registeredAddressId],
            ]);

        $this->helperAddress->expects($this->any())
            ->method('getTaxCalculationAddressType')
            ->willReturn($configAddressType);

        $this->model->execute($observer);
    }

    /**
     * @return array
     */
    public function dataProviderAfterAddressSaveRestricted()
    {
        return [
            [false, false, false, 1, null, null],
            [true, true, false, 1, null, null],
            [true, false, false, 1, null, null],
            [true, false, false, 1, 1, AbstractAddress::TYPE_BILLING],
            [true, false, false, 1, 1, AbstractAddress::TYPE_SHIPPING],
        ];
    }

    public function testAfterAddressSaveException()
    {
        $store = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->getMock();

        $customer = $this->getMockBuilder(Customer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $customer->expects($this->any())
            ->method('getStore')
            ->willReturn($store);

        $address = $this->getMockBuilder(\Magento\Customer\Model\Address::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getCustomer',
                'getForceProcess',
                'getVatId',
            ])
            ->getMock();
        $address->expects($this->any())
            ->method('getCustomer')
            ->willReturn($customer);
        $address->expects($this->once())
            ->method('getForceProcess')
            ->willReturn(true);
        $address->expects($this->any())
            ->method('getVatId')
            ->willThrowException(new \Exception('Exception'));

        $observer = $this->getMockBuilder(Observer::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getCustomerAddress',
            ])
            ->getMock();
        $observer->expects($this->once())
            ->method('getCustomerAddress')
            ->willReturn($address);

        $this->helperAddress->expects($this->once())
            ->method('isVatValidationEnabled')
            ->with($store)
            ->willReturn(true);

        $this->registry->expects($this->any())
            ->method('registry')
            ->with(AfterAddressSaveObserver::VIV_PROCESSED_FLAG)
            ->willReturn(false);
        $this->registry->expects($this->any())
            ->method('register')
            ->willReturnMap([
                [AfterAddressSaveObserver::VIV_PROCESSED_FLAG, true, false, $this->registry],
                [AfterAddressSaveObserver::VIV_PROCESSED_FLAG, false, true, $this->registry],
            ]);

        $this->model->execute($observer);
    }

    /**
     * @param string $vatId
     * @param int $countryId
     * @param bool $isCountryInEU
     * @param int $defaultGroupId
     * @dataProvider dataProviderAfterAddressSaveDefaultGroup
     */
    public function testAfterAddressSaveDefaultGroup(
        $vatId,
        $countryId,
        $isCountryInEU,
        $defaultGroupId
    ) {
        $store = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->getMock();

        $dataGroup = $this->getMockBuilder(GroupInterface::class)
            ->getMockForAbstractClass();
        $dataGroup->expects($this->any())
            ->method('getId')
            ->willReturn($defaultGroupId);

        $customer = $this->getMockBuilder(Customer::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getStore',
                'getDisableAutoGroupChange',
                'getGroupId',
                'setGroupId',
                'save',
            ])
            ->getMock();
        $customer->expects($this->exactly(2))
            ->method('getStore')
            ->willReturn($store);
        $customer->expects($this->once())
            ->method('getDisableAutoGroupChange')
            ->willReturn(false);
        $customer->expects($this->once())
            ->method('getGroupId')
            ->willReturn(null);
        $customer->expects($this->once())
            ->method('setGroupId')
            ->with($defaultGroupId)
            ->willReturnSelf();
        $customer->expects($this->once())
            ->method('save')
            ->willReturnSelf();

        $address = $this->getMockBuilder(\Magento\Customer\Model\Address::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getCustomer',
                'getForceProcess',
                'getVatId',
                'getCountry',
            ])
            ->getMock();
        $address->expects($this->once())
            ->method('getCustomer')
            ->willReturn($customer);
        $address->expects($this->once())
            ->method('getForceProcess')
            ->willReturn(true);
        $address->expects($this->once())
            ->method('getVatId')
            ->willReturn($vatId);
        $address->expects($this->any())
            ->method('getCountry')
            ->willReturn($countryId);

        $observer = $this->getMockBuilder(Observer::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getCustomerAddress',
            ])
            ->getMock();
        $observer->expects($this->once())
            ->method('getCustomerAddress')
            ->willReturn($address);

        $this->helperAddress->expects($this->once())
            ->method('isVatValidationEnabled')
            ->with($store)
            ->willReturn(true);

        $this->vat->expects($this->any())
            ->method('isCountryInEU')
            ->with($countryId)
            ->willReturn($isCountryInEU);

        $this->groupManagement->expects($this->any())
            ->method('getDefaultGroup')
            ->with($store)
            ->willReturn($dataGroup);

        $this->model->execute($observer);
    }

    /**
     * @return array
     */
    public function dataProviderAfterAddressSaveDefaultGroup()
    {
        return [
            ['', 1, false, 1],
            [1, 1, false, 1],
        ];
    }

    /**
     * @param string $vatId
     * @param $vatClass
     * @param int $countryId
     * @param string $country
     * @param int $newGroupId
     * @param string $areaCode
     * @param bool $resultVatIsValid
     * @param bool $resultRequestSuccess
     * @param string $resultValidMessage
     * @param string $resultInvalidMessage
     * @param string $resultErrorMessage
     * @dataProvider dataProviderAfterAddressSaveNewGroup
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function testAfterAddressSaveNewGroup(
        $vatId,
        $vatClass,
        $countryId,
        $country,
        $newGroupId,
        $areaCode,
        $resultVatIsValid,
        $resultRequestSuccess,
        $resultValidMessage,
        $resultInvalidMessage,
        $resultErrorMessage
    ) {
        $store = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->getMock();

        $validationResult = $this->getMockBuilder(DataObject::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getIsValid',
                'getRequestSuccess',
            ])
            ->getMock();
        $validationResult->expects($this->any())
            ->method('getIsValid')
            ->willReturn($resultVatIsValid);
        $validationResult->expects($this->any())
            ->method('getRequestSuccess')
            ->willReturn($resultRequestSuccess);

        $customer = $this->getMockBuilder(Customer::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getStore',
                'getDisableAutoGroupChange',
                'getGroupId',
                'setGroupId',
                'save',
            ])
            ->getMock();
        $customer->expects($this->exactly(2))
            ->method('getStore')
            ->willReturn($store);
        $customer->expects($this->any())
            ->method('getDisableAutoGroupChange')
            ->willReturn(false);
        $customer->expects($this->once())
            ->method('getGroupId')
            ->willReturn(null);
        $customer->expects($this->once())
            ->method('setGroupId')
            ->with($newGroupId)
            ->willReturnSelf();
        $customer->expects($this->once())
            ->method('save')
            ->willReturnSelf();

        $this->customerSessionMock->expects($this->once())
            ->method('setCustomerGroupId')
            ->with($newGroupId)
            ->willReturnSelf();

        $address = $this->getMockBuilder(\Magento\Customer\Model\Address::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getCustomer',
                'getForceProcess',
                'getVatId',
                'getCountryId',
                'getCountry',
                'setVatValidationResult',
            ])
            ->getMock();
        $address->expects($this->any())
            ->method('getCustomer')
            ->willReturn($customer);
        $address->expects($this->once())
            ->method('getForceProcess')
            ->willReturn(true);
        $address->expects($this->any())
            ->method('getVatId')
            ->willReturn($vatId);
        $address->expects($this->any())
            ->method('getCountryId')
            ->willReturn($countryId);
        $address->expects($this->once())
            ->method('getCountry')
            ->willReturn($country);
        $address->expects($this->once())
            ->method('setVatValidationResult')
            ->with($validationResult)
            ->willReturnSelf();

        $observer = $this->getMockBuilder(Observer::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getCustomerAddress',
            ])
            ->getMock();
        $observer->expects($this->once())
            ->method('getCustomerAddress')
            ->willReturn($address);

        $this->helperAddress->expects($this->once())
            ->method('isVatValidationEnabled')
            ->with($store)
            ->willReturn(true);

        $this->vat->expects($this->any())
            ->method('isCountryInEU')
            ->with($country)
            ->willReturn(true);
        $this->vat->expects($this->once())
            ->method('checkVatNumber')
            ->with($countryId, $vatId)
            ->willReturn($validationResult);
        $this->vat->expects($this->once())
            ->method('getCustomerGroupIdBasedOnVatNumber')
            ->with($countryId, $validationResult, $store)
            ->willReturn($newGroupId);
        $this->vat->expects($this->any())
            ->method('getCustomerVatClass')
            ->with($countryId, $validationResult)
            ->willReturn($vatClass);

        $this->appState->expects($this->once())
            ->method('getAreaCode')
            ->willReturn($areaCode);

        $this->scopeConfig->expects($this->any())
            ->method('isSetFlag')
            ->with(HelperAddress::XML_PATH_VIV_DISABLE_AUTO_ASSIGN_DEFAULT)
            ->willReturn(false);

        if ($resultValidMessage) {
            $this->messageManager->expects($this->once())
                ->method('addSuccess')
                ->with($resultValidMessage)
                ->willReturnSelf();
        }
        if ($resultInvalidMessage) {
            $this->escaper->expects($this->once())
                ->method('escapeHtml')
                ->with($vatId)
                ->willReturn($vatId);
            $this->messageManager->expects($this->once())
                ->method('addErrorMessage')
                ->with($resultInvalidMessage)
                ->willReturnSelf();
        }
        if ($resultErrorMessage) {
            $this->scopeConfig->expects($this->once())
                ->method('getValue')
                ->with('trans_email/ident_support/email', ScopeInterface::SCOPE_STORE)
                ->willReturn('admin@example.com');
            $this->messageManager->expects($this->once())
                ->method('addErrorMessage')
                ->with($resultErrorMessage)
                ->willReturnSelf();
        }

        $this->model->execute($observer);
    }

    /**
     * @return array
     */
    public function dataProviderAfterAddressSaveNewGroup()
    {
        return [
            [
                'vat_id' => 1,
                'vat_class' => null,
                'country_id' => 1,
                'country_code' => 'US',
                'group_id' => 1,
                'area_code' => Area::AREA_ADMINHTML,
                'is_vat_valid' => false,
                'request_sucess' => false,
                'valid_message' => '',
                'invalid_message' => '',
                'error_message' => '',
            ],
            [
                'vat_id' => 1,
                'vat_class' => Vat::VAT_CLASS_DOMESTIC,
                'country_id' => 1,
                'country_code' => 'US',
                'group_id' => 1,
                'area_code' => Area::AREA_FRONTEND,
                'is_vat_valid' => true,
                'request_sucess' => false,
                'valid_message' => 'Your VAT ID was successfully validated. You will be charged tax.',
                'invalid_message' => '',
                'error_message' => '',
            ],
            [
                'vat_id' => 1,
                'vat_class' => Vat::VAT_CLASS_INTRA_UNION,
                'country_id' => 1,
                'country_code' => 'US',
                'group_id' => 1,
                'area_code' => Area::AREA_FRONTEND,
                'is_vat_valid' => true,
                'request_sucess' => false,
                'valid_message' => 'Your VAT ID was successfully validated. You will not be charged tax.',
                'invalid_message' => '',
                'error_message' => '',
            ],
            [
                'vat_id' => 1,
                'vat_class' => Vat::VAT_CLASS_INTRA_UNION,
                'country_id' => 1,
                'country_code' => 'US',
                'group_id' => 1,
                'area_code' => Area::AREA_FRONTEND,
                'is_vat_valid' => false,
                'request_sucess' => true,
                'valid_message' => '',
                'invalid_message' => 'The VAT ID entered (1) is not a valid VAT ID. You will be charged tax.',
                'error_message' => '',
            ],
            [
                'vat_id' => 1,
                'vat_class' => Vat::VAT_CLASS_INTRA_UNION,
                'country_id' => 1,
                'country_code' => 'US',
                'group_id' => 1,
                'area_code' => Area::AREA_FRONTEND,
                'is_vat_valid' => false,
                'request_sucess' => false,
                'valid_message' => '',
                'invalid_message' => '',
                'error_message' => 'Your Tax ID cannot be validated. You will be charged tax. '
                    . 'If you believe this is an error, please contact us at admin@example.com',
            ],
        ];
    }
}
