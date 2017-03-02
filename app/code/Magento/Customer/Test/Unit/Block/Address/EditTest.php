<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Block\Address;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class EditTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    /**
     * @var \Magento\Customer\Api\AddressRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $addressRepositoryMock;

    /**
     * @var \Magento\Customer\Model\Session|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerSessionMock;

    /**
     * @var \Magento\Framework\View\Page\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $pageConfigMock;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $dataObjectHelperMock;

    /**
     * @var \Magento\Customer\Api\Data\AddressInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $addressDataFactoryMock;

    /**
     * @var \Magento\Customer\Helper\Session\CurrentCustomer|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $currentCustomerMock;

    /**
     * @var \Magento\Customer\Block\Address\Edit
     */
    protected $model;

    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->requestMock = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->getMock();

        $this->addressRepositoryMock = $this->getMockBuilder(\Magento\Customer\Api\AddressRepositoryInterface::class)
            ->getMock();

        $this->customerSessionMock = $this->getMockBuilder(\Magento\Customer\Model\Session::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAddressFormData', 'getCustomerId'])
            ->getMock();

        $this->pageConfigMock = $this->getMockBuilder(\Magento\Framework\View\Page\Config::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->dataObjectHelperMock = $this->getMockBuilder(\Magento\Framework\Api\DataObjectHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->addressDataFactoryMock = $this->getMockBuilder(\Magento\Customer\Api\Data\AddressInterfaceFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->currentCustomerMock = $this->getMockBuilder(\Magento\Customer\Helper\Session\CurrentCustomer::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = $this->objectManager->getObject(
            \Magento\Customer\Block\Address\Edit::class,
            [
                'request' => $this->requestMock,
                'addressRepository' => $this->addressRepositoryMock,
                'customerSession' => $this->customerSessionMock,
                'pageConfig' => $this->pageConfigMock,
                'dataObjectHelper' => $this->dataObjectHelperMock,
                'addressDataFactory' => $this->addressDataFactoryMock,
                'currentCustomer' => $this->currentCustomerMock,
            ]
        );
    }

    public function testSetLayoutWithOwnAddressAndPostedData()
    {
        $addressId = 1;
        $customerId = 1;
        $title = __('Edit Address');
        $postedData = [
            'region_id' => 1,
            'region' => 'region',
        ];
        $newPostedData = $postedData;
        $newPostedData['region'] = $postedData;

        $layoutMock = $this->getMockBuilder(\Magento\Framework\View\LayoutInterface::class)
            ->getMock();

        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('id', null)
            ->willReturn($addressId);

        $addressMock = $this->getMockBuilder(\Magento\Customer\Api\Data\AddressInterface::class)
            ->getMock();
        $this->addressRepositoryMock->expects($this->once())
            ->method('getById')
            ->with($addressId)
            ->willReturn($addressMock);

        $addressMock->expects($this->once())
            ->method('getCustomerId')
            ->willReturn($customerId);

        $this->customerSessionMock->expects($this->at(0))
            ->method('getCustomerId')
            ->willReturn($customerId);

        $addressMock->expects($this->exactly(2))
            ->method('getId')
            ->willReturn($addressId);

        $pageTitleMock = $this->getMockBuilder(\Magento\Framework\View\Page\Title::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->pageConfigMock->expects($this->once())
            ->method('getTitle')
            ->willReturn($pageTitleMock);

        $pageTitleMock->expects($this->once())
            ->method('set')
            ->with($title)
            ->willReturnSelf();

        $this->customerSessionMock->expects($this->at(1))
            ->method('getAddressFormData')
            ->with(true)
            ->willReturn($postedData);

        $this->dataObjectHelperMock->expects($this->once())
            ->method('populateWithArray')
            ->with(
                $addressMock,
                $newPostedData,
                \Magento\Customer\Api\Data\AddressInterface::class
            )->willReturnSelf();

        $this->assertEquals($this->model, $this->model->setLayout($layoutMock));
        $this->assertEquals($layoutMock, $this->model->getLayout());
    }

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testSetLayoutWithAlienAddress()
    {
        $addressId = 1;
        $customerId = 1;
        $customerPrefix = 'prefix';
        $customerFirstName = 'firstname';
        $customerMiddlename = 'middlename';
        $customerLastname = 'lastname';
        $customerSuffix = 'suffix';
        $title = __('Add New Address');

        $layoutMock = $this->getMockBuilder(\Magento\Framework\View\LayoutInterface::class)
            ->getMock();

        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('id', null)
            ->willReturn($addressId);

        $addressMock = $this->getMockBuilder(\Magento\Customer\Api\Data\AddressInterface::class)
            ->getMock();
        $this->addressRepositoryMock->expects($this->once())
            ->method('getById')
            ->with($addressId)
            ->willReturn($addressMock);

        $addressMock->expects($this->once())
            ->method('getCustomerId')
            ->willReturn($customerId);

        $this->customerSessionMock->expects($this->at(0))
            ->method('getCustomerId')
            ->willReturn($customerId + 1);

        $newAddressMock = $this->getMockBuilder(\Magento\Customer\Api\Data\AddressInterface::class)
            ->getMock();
        $this->addressDataFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($newAddressMock);

        $customerMock = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)
            ->getMock();
        $this->currentCustomerMock->expects($this->once())
            ->method('getCustomer')
            ->willReturn($customerMock);

        $customerMock->expects($this->once())
            ->method('getPrefix')
            ->willReturn($customerPrefix);
        $customerMock->expects($this->once())
            ->method('getFirstname')
            ->willReturn($customerFirstName);
        $customerMock->expects($this->once())
            ->method('getMiddlename')
            ->willReturn($customerMiddlename);
        $customerMock->expects($this->once())
            ->method('getLastname')
            ->willReturn($customerLastname);
        $customerMock->expects($this->once())
            ->method('getSuffix')
            ->willReturn($customerSuffix);

        $newAddressMock->expects($this->once())
            ->method('setPrefix')
            ->with($customerPrefix)
            ->willReturnSelf();
        $newAddressMock->expects($this->once())
            ->method('setFirstname')
            ->with($customerFirstName)
            ->willReturnSelf();
        $newAddressMock->expects($this->once())
            ->method('setMiddlename')
            ->with($customerMiddlename)
            ->willReturnSelf();
        $newAddressMock->expects($this->once())
            ->method('setLastname')
            ->with($customerLastname)
            ->willReturnSelf();
        $newAddressMock->expects($this->once())
            ->method('setSuffix')
            ->with($customerSuffix)
            ->willReturnSelf();

        $newAddressMock->expects($this->once())
            ->method('getId')
            ->willReturn(null);

        $pageTitleMock = $this->getMockBuilder(\Magento\Framework\View\Page\Title::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->pageConfigMock->expects($this->once())
            ->method('getTitle')
            ->willReturn($pageTitleMock);

        $pageTitleMock->expects($this->once())
            ->method('set')
            ->with($title)
            ->willReturnSelf();

        $this->assertEquals($this->model, $this->model->setLayout($layoutMock));
        $this->assertEquals($layoutMock, $this->model->getLayout());
    }

    public function testSetLayoutWithoutAddressId()
    {
        $customerPrefix = 'prefix';
        $customerFirstName = 'firstname';
        $customerMiddlename = 'middlename';
        $customerLastname = 'lastname';
        $customerSuffix = 'suffix';
        $title = 'title';

        $layoutMock = $this->getMockBuilder(\Magento\Framework\View\LayoutInterface::class)
            ->getMock();

        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('id', null)
            ->willReturn('');

        $newAddressMock = $this->getMockBuilder(\Magento\Customer\Api\Data\AddressInterface::class)
            ->getMock();
        $this->addressDataFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($newAddressMock);

        $customerMock = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)
            ->getMock();
        $this->currentCustomerMock->expects($this->once())
            ->method('getCustomer')
            ->willReturn($customerMock);

        $customerMock->expects($this->once())
            ->method('getPrefix')
            ->willReturn($customerPrefix);
        $customerMock->expects($this->once())
            ->method('getFirstname')
            ->willReturn($customerFirstName);
        $customerMock->expects($this->once())
            ->method('getMiddlename')
            ->willReturn($customerMiddlename);
        $customerMock->expects($this->once())
            ->method('getLastname')
            ->willReturn($customerLastname);
        $customerMock->expects($this->once())
            ->method('getSuffix')
            ->willReturn($customerSuffix);

        $newAddressMock->expects($this->once())
            ->method('setPrefix')
            ->with($customerPrefix)
            ->willReturnSelf();
        $newAddressMock->expects($this->once())
            ->method('setFirstname')
            ->with($customerFirstName)
            ->willReturnSelf();
        $newAddressMock->expects($this->once())
            ->method('setMiddlename')
            ->with($customerMiddlename)
            ->willReturnSelf();
        $newAddressMock->expects($this->once())
            ->method('setLastname')
            ->with($customerLastname)
            ->willReturnSelf();
        $newAddressMock->expects($this->once())
            ->method('setSuffix')
            ->with($customerSuffix)
            ->willReturnSelf();

        $pageTitleMock = $this->getMockBuilder(\Magento\Framework\View\Page\Title::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->pageConfigMock->expects($this->once())
            ->method('getTitle')
            ->willReturn($pageTitleMock);

        $this->model->setData('title', $title);

        $pageTitleMock->expects($this->once())
            ->method('set')
            ->with($title)
            ->willReturnSelf();

        $this->assertEquals($this->model, $this->model->setLayout($layoutMock));
        $this->assertEquals($layoutMock, $this->model->getLayout());
    }

    public function testSetLayoutWithoutAddress()
    {
        $addressId = 1;
        $customerPrefix = 'prefix';
        $customerFirstName = 'firstname';
        $customerMiddlename = 'middlename';
        $customerLastname = 'lastname';
        $customerSuffix = 'suffix';
        $title = 'title';

        $layoutMock = $this->getMockBuilder(\Magento\Framework\View\LayoutInterface::class)
            ->getMock();

        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('id', null)
            ->willReturn($addressId);

        $this->addressRepositoryMock->expects($this->once())
            ->method('getById')
            ->with($addressId)
            ->willThrowException(
                \Magento\Framework\Exception\NoSuchEntityException::singleField('addressId', $addressId)
            );

        $newAddressMock = $this->getMockBuilder(\Magento\Customer\Api\Data\AddressInterface::class)
            ->getMock();
        $this->addressDataFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($newAddressMock);

        $customerMock = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)
            ->getMock();
        $this->currentCustomerMock->expects($this->once())
            ->method('getCustomer')
            ->willReturn($customerMock);

        $customerMock->expects($this->once())
            ->method('getPrefix')
            ->willReturn($customerPrefix);
        $customerMock->expects($this->once())
            ->method('getFirstname')
            ->willReturn($customerFirstName);
        $customerMock->expects($this->once())
            ->method('getMiddlename')
            ->willReturn($customerMiddlename);
        $customerMock->expects($this->once())
            ->method('getLastname')
            ->willReturn($customerLastname);
        $customerMock->expects($this->once())
            ->method('getSuffix')
            ->willReturn($customerSuffix);

        $newAddressMock->expects($this->once())
            ->method('setPrefix')
            ->with($customerPrefix)
            ->willReturnSelf();
        $newAddressMock->expects($this->once())
            ->method('setFirstname')
            ->with($customerFirstName)
            ->willReturnSelf();
        $newAddressMock->expects($this->once())
            ->method('setMiddlename')
            ->with($customerMiddlename)
            ->willReturnSelf();
        $newAddressMock->expects($this->once())
            ->method('setLastname')
            ->with($customerLastname)
            ->willReturnSelf();
        $newAddressMock->expects($this->once())
            ->method('setSuffix')
            ->with($customerSuffix)
            ->willReturnSelf();

        $pageTitleMock = $this->getMockBuilder(\Magento\Framework\View\Page\Title::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->pageConfigMock->expects($this->once())
            ->method('getTitle')
            ->willReturn($pageTitleMock);

        $this->model->setData('title', $title);

        $pageTitleMock->expects($this->once())
            ->method('set')
            ->with($title)
            ->willReturnSelf();

        $this->assertEquals($this->model, $this->model->setLayout($layoutMock));
        $this->assertEquals($layoutMock, $this->model->getLayout());
    }
}
