<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Braintree\Test\Unit\Block\Creditcard;

use Magento\Braintree\Block\Creditcard\Management;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class ManagementTest
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ManagementTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Braintree\Block\Creditcard\Management
     */
    protected $block;

    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var \Magento\Directory\Model\ResourceModel\Region\CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $regionCollectionFactoryMock;

    /**
     * @var \Magento\Payment\Model\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $paymentConfigMock;

    /**
     * @var \Magento\Braintree\Model\Vault|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $vaultMock;

    /**
     * @var \Magento\Braintree\Model\Config\Cc|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $configMock;

    /**
     * @var \Magento\Customer\Model\Session|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerSessionMock;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerRepositoryMock;

    /**
     * @var \Magento\Payment\Model\CcConfig|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $ccConfigMock;

    /**
     * @var \Magento\Braintree\Helper\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $dataHelperMock;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    /**
     * @var \Magento\Framework\View\LayoutInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $layoutMock;

    protected function setUp()
    {
        $this->regionCollectionFactoryMock = $this->getMockBuilder(
            '\Magento\Directory\Model\ResourceModel\Region\CollectionFactory'
        )->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->paymentConfigMock = $this->getMockBuilder(
            '\Magento\Payment\Model\Config'
        )->disableOriginalConstructor()
            ->getMock();
        $this->configMock = $this->getMockBuilder('\Magento\Braintree\Model\Config\Cc')
            ->disableOriginalConstructor()
            ->getMock();
        $this->vaultMock = $this->getMockBuilder('\Magento\Braintree\Model\Vault')
            ->disableOriginalConstructor()
            ->getMock();
        $this->customerSessionMock = $this->getMockBuilder('\Magento\Customer\Model\Session')
            ->disableOriginalConstructor()
            ->getMock();
        $this->customerRepositoryMock = $this->getMock('\Magento\Customer\Api\CustomerRepositoryInterface');
        $this->ccConfigMock = $this->getMockBuilder('\Magento\Payment\Model\CcConfig')
            ->disableOriginalConstructor()
            ->getMock();
        $this->dataHelperMock = $this->getMockBuilder('\Magento\Braintree\Helper\Data')
            ->disableOriginalConstructor()
            ->getMock();

        $this->requestMock = $this->getMock('\Magento\Framework\App\RequestInterface');
        $this->layoutMock = $this->getMock('\Magento\Framework\View\LayoutInterface');
        $contextMock = $this->getMockBuilder('\Magento\Framework\View\Element\Template\Context')
            ->disableOriginalConstructor()
            ->getMock();
        $contextMock->expects($this->any())
            ->method('getRequest')
            ->willReturn($this->requestMock);
        $contextMock->expects($this->any())
            ->method('getLayout')
            ->willReturn($this->layoutMock);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->block = $this->objectManagerHelper->getObject(
            '\Magento\Braintree\Block\Creditcard\Management',
            [
                'context' => $contextMock,
                'regionCollectionFactory' => $this->regionCollectionFactoryMock,
                'paymentConfig' => $this->paymentConfigMock,
                'vault' => $this->vaultMock,
                'config' => $this->configMock,
                'customerSession' => $this->customerSessionMock,
                'customerRepository' => $this->customerRepositoryMock,
                'ccConfig' => $this->ccConfigMock,
                'dataHelper' => $this->dataHelperMock,
            ]
        );
    }

    public function testCreditCard()
    {
        $token = 'token';
        $creditCard = 'creditCard';

        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('token')
            ->willReturn($token);

        $this->vaultMock->expects($this->once())
            ->method('storedCard')
            ->with($token)
            ->willReturn($creditCard);

        $this->assertEquals($creditCard, $this->block->creditCard());
    }

    /**
     * @dataProvider getTitleDataProvider
     */
    public function testGetTitle($type, $expected)
    {
        $this->block->setType($type);

        $title = new \Magento\Framework\Phrase($expected);

        $this->assertEquals($title, $this->block->getTitle());
    }

    public function getTitleDataProvider()
    {
        return [
            'edit' => [
                'type' => Management::TYPE_EDIT,
                'expected' => 'Edit Credit Card',
            ],
            'other' => [
                'type' => null,
                'expected' => 'Add Credit Card',
            ],
        ];
    }

    public function testIsEditMode()
    {
        $this->block->setType(Management::TYPE_EDIT);
        $this->assertTrue($this->block->isEditMode());

        $this->block->setType(null);
        $this->assertFalse($this->block->isEditMode());
    }

    public function testCountrySelect()
    {
        $html = "<select>";
        $default = 'default';
        $name = 'US';
        $id = '3';
        $title = "United States";

        $directoryDataMock = $this->getMockBuilder('\Magento\Braintree\Block\Directory\Data')
            ->disableOriginalConstructor()
            ->getMock();

        $this->layoutMock->expects($this->any())
            ->method('getChildName')
            ->willReturn(true);
        $this->layoutMock->expects($this->any())
            ->method('getBlock')
            ->willReturn($directoryDataMock);
        $directoryDataMock->expects($this->once())
            ->method('getCountryHtmlSelect')
            ->with($default, $name, $id, $title)
            ->willReturn($html);

        $this->assertEquals($html, $this->block->countrySelect($name, $id, $default, $title));
    }

    public function testGetRegionIdByName()
    {
        $regionCode = 'TX';
        $countryId = 'US';
        $regionId = 57;

        $regionCollectionMock = $this->getMockBuilder('\Magento\Directory\Model\ResourceModel\Region\Collection')
            ->disableOriginalConstructor()
            ->getMock();
        $this->regionCollectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($regionCollectionMock);

        $regionCollectionMock->expects($this->once())
            ->method('addRegionCodeOrNameFilter')
            ->with($regionCode)
            ->willReturnSelf();
        $regionCollectionMock->expects($this->once())
            ->method('addCountryFilter')
            ->with($countryId)
            ->willReturnSelf();

        $regionCollectionMock->expects($this->once())
            ->method('getSize')
            ->willReturn(1);
        $regionCollectionMock->expects($this->once())
            ->method('getFirstItem')
            ->willReturn(new \Magento\Framework\DataObject(['id' => $regionId]));

        $this->assertEquals($regionId, $this->block->getRegionIdByName($regionCode, $countryId));
    }

    public function testGetCcMonths()
    {
        $months = [
            1 => '01 - January',
            2 => '02 - February',
        ];
        $expected = [
            0 => new \Magento\Framework\Phrase('Month'),
            1 => '01 - January',
            2 => '02 - February',
        ];
        $this->paymentConfigMock->expects($this->once())
            ->method('getMonths')
            ->willReturn($months);

        $this->assertEquals($expected, $this->block->getCcMonths());
        //Call again, should use cached copy
        $this->assertEquals($expected, $this->block->getCcMonths());
    }

    public function testGetCcYear()
    {
        $year = [
            2015 => 2015,
            2016 => 2016,
        ];
        $expected = [
            0 => new \Magento\Framework\Phrase('Year'),
            2015 => 2015,
            2016 => 2016,
        ];
        $this->paymentConfigMock->expects($this->once())
            ->method('getYears')
            ->willReturn($year);

        $this->assertEquals($expected, $this->block->getCcYears());
    }

    public function testGetCurrentCustomerStoredCards()
    {
        $cards = ['card'];
        $this->vaultMock->expects($this->once())
            ->method('currentCustomerStoredCards')
            ->willReturn($cards);

        $this->assertEquals($cards, $this->block->getCurrentCustomerStoredCards());
    }

    public function testGetCustomer()
    {
        $firstName = 'John';
        $lastName = 'Doe';

        $customerId = 1003;
        $customer = new \Magento\Framework\DataObject(
            [
                'firstname' => $firstName,
                'lastname' => $lastName,
            ]
        );

        $this->customerSessionMock->expects($this->once())
            ->method('getCustomerId')
            ->willReturn($customerId);
        $this->customerRepositoryMock->expects($this->once())
            ->method('getById')
            ->with($customerId)
            ->willReturn($customer);
        $this->configMock->expects($this->any())
            ->method('useVault')
            ->willReturn(true);
        $this->customerSessionMock->expects($this->any())
            ->method('isLoggedIn')
            ->willReturn(true);

        $this->assertEquals($firstName, $this->block->currentCustomerName());
        $this->assertEquals($lastName, $this->block->currentCustomerLastName());
    }

    /**
     * @dataProvider getCustomerEmptyDataProvider
     */
    public function testGetCustomerEmpty($useVault, $isLoggedIn)
    {
        $this->configMock->expects($this->any())
            ->method('useVault')
            ->willReturn($useVault);
        $this->customerSessionMock->expects($this->any())
            ->method('isLoggedIn')
            ->willReturn($isLoggedIn);

        $this->assertEquals('', $this->block->currentCustomerName());
        $this->assertEquals('', $this->block->currentCustomerLastName());
    }
    public function getCustomerEmptyDataProvider()
    {
        return [
            'no_vault' => [
                'use_vault' => false,
                'loggedin' => true,
            ],
            'not_logged_in' => [
                'use_vault' => true,
                'loggedin' => false,
            ],
        ];
    }
}
