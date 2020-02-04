<?php
/**
 * Unit test for customer service layer \Magento\Customer\Model\Customer
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test class for \Magento\Customer\Model\Customer testing
 */
namespace Magento\Customer\Test\Unit\Model;

use Magento\Customer\Model\Customer;
use Magento\Customer\Model\AccountConfirmation;
use Magento\Customer\Model\ResourceModel\Address\CollectionFactory as AddressCollectionFactory;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Framework\Math\Random;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class CustomerTest extends \PHPUnit\Framework\TestCase
{
    /** @var Customer */
    protected $_model;

    /** @var \Magento\Store\Model\Website|\PHPUnit_Framework_MockObject_MockObject */
    protected $_website;

    /** @var \Magento\Store\Model\StoreManager|\PHPUnit_Framework_MockObject_MockObject */
    protected $_storeManager;

    /** @var \Magento\Eav\Model\Config|\PHPUnit_Framework_MockObject_MockObject */
    protected $_config;

    /** @var \Magento\Eav\Model\Attribute|\PHPUnit_Framework_MockObject_MockObject */
    protected $_attribute;

    /** @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $_scopeConfigMock;

    /** @var \Magento\Framework\Mail\Template\TransportBuilder|\PHPUnit_Framework_MockObject_MockObject */
    protected $_transportBuilderMock;

    /** @var \Magento\Framework\Mail\TransportInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $_transportMock;

    /** @var \Magento\Framework\Encryption\EncryptorInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $_encryptor;

    /** @var \Magento\Customer\Model\AttributeFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $attributeFactoryMock;

    /** @var  \Magento\Customer\Model\Attribute|\PHPUnit_Framework_MockObject_MockObject */
    protected $attributeCustomerMock;

    /** @var  \Magento\Framework\Registry|\PHPUnit_Framework_MockObject_MockObject */
    protected $registryMock;

    /** @var \Magento\Customer\Model\ResourceModel\Customer|\PHPUnit_Framework_MockObject_MockObject */
    protected $resourceMock;

    /**
     * @var \Magento\Framework\Reflection\DataObjectProcessor|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dataObjectProcessor;

    /**
     * @var AccountConfirmation|\PHPUnit_Framework_MockObject_MockObject
     */
    private $accountConfirmation;

    /**
     * @var AddressCollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $addressesFactory;

    /**
     * @var CustomerInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerDataFactory;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dataObjectHelper;

    /**
     * @var Random|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mathRandom;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->_website = $this->createMock(\Magento\Store\Model\Website::class);
        $this->_config = $this->createMock(\Magento\Eav\Model\Config::class);
        $this->_attribute = $this->createMock(\Magento\Eav\Model\Attribute::class);
        $this->_storeManager = $this->createMock(\Magento\Store\Model\StoreManager::class);
        $this->_storetMock = $this->createMock(\Magento\Store\Model\Store::class);
        $this->_scopeConfigMock = $this->createMock(\Magento\Framework\App\Config\ScopeConfigInterface::class);
        $this->_transportBuilderMock = $this->createMock(\Magento\Framework\Mail\Template\TransportBuilder::class);
        $this->_transportMock = $this->createMock(\Magento\Framework\Mail\TransportInterface::class);
        $this->attributeFactoryMock = $this->createPartialMock(
            \Magento\Customer\Model\AttributeFactory::class,
            ['create']
        );
        $this->attributeCustomerMock = $this->createMock(\Magento\Customer\Model\Attribute::class);
        $this->resourceMock = $this->createPartialMock(
            \Magento\Customer\Model\ResourceModel\Customer::class, // \Magento\Framework\DataObject::class,
            ['getIdFieldName']
        );

        $this->dataObjectProcessor = $this->createPartialMock(
            \Magento\Framework\Reflection\DataObjectProcessor::class,
            ['buildOutputDataArray']
        );

        $this->resourceMock->expects($this->any())
            ->method('getIdFieldName')
            ->will($this->returnValue('id'));
        $this->registryMock = $this->createPartialMock(\Magento\Framework\Registry::class, ['registry']);
        $this->_encryptor = $this->createMock(\Magento\Framework\Encryption\EncryptorInterface::class);
        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->accountConfirmation = $this->createMock(AccountConfirmation::class);
        $this->addressesFactory = $this->getMockBuilder(AddressCollectionFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->customerDataFactory = $this->getMockBuilder(CustomerInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->dataObjectHelper = $this->getMockBuilder(\Magento\Framework\Api\DataObjectHelper::class)
            ->disableOriginalConstructor()
            ->setMethods(['populateWithArray'])
            ->getMock();
        $this->mathRandom = $this->createMock(Random::class);

        $this->_model = $helper->getObject(
            \Magento\Customer\Model\Customer::class,
            [
                'storeManager' => $this->_storeManager,
                'config' => $this->_config,
                'transportBuilder' => $this->_transportBuilderMock,
                'scopeConfig' => $this->_scopeConfigMock,
                'encryptor' => $this->_encryptor,
                'attributeFactory' => $this->attributeFactoryMock,
                'registry' => $this->registryMock,
                'resource' => $this->resourceMock,
                'dataObjectProcessor' => $this->dataObjectProcessor,
                'accountConfirmation' => $this->accountConfirmation,
                '_addressesFactory' => $this->addressesFactory,
                'customerDataFactory' => $this->customerDataFactory,
                'dataObjectHelper' => $this->dataObjectHelper,
                'mathRandom' => $this->mathRandom,
            ]
        );
    }

    public function testHashPassword()
    {
        $this->_encryptor->expects(
            $this->once()
        )->method(
            'getHash'
        )->with(
            'password',
            'salt'
        )->will(
            $this->returnValue('hash')
        );
        $this->assertEquals('hash', $this->_model->hashPassword('password', 'salt'));
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage The transactional account email type is incorrect. Verify and try again.
     */
    public function testSendNewAccountEmailException()
    {
        $this->_model->sendNewAccountEmail('test');
    }

    public function testSendNewAccountEmailWithoutStoreId()
    {
        $store = $this->createMock(\Magento\Store\Model\Store::class);
        $website = $this->createMock(\Magento\Store\Model\Website::class);
        $website->expects($this->once())
            ->method('getStoreIds')
            ->will($this->returnValue([1, 2, 3, 4]));
        $this->_storeManager->expects($this->once())
            ->method('getWebsite')
            ->with(1)
            ->will($this->returnValue($website));
        $this->_storeManager->expects($this->once())
            ->method('getStore')
            ->with(1)
            ->will($this->returnValue($store));

        $this->_config->expects($this->exactly(3))
            ->method('getAttribute')
            ->will($this->returnValue($this->_attribute));

        $this->_attribute->expects($this->exactly(3))
            ->method('getIsVisible')
            ->will($this->returnValue(true));

        $methods = [
            'setTemplateIdentifier',
            'setTemplateOptions',
            'setTemplateVars',
            'setFrom',
            'addTo',
        ];
        foreach ($methods as $method) {
            $this->_transportBuilderMock->expects($this->once())
                ->method($method)
                ->will($this->returnSelf());
        }
        $transportMock = $this->createMock(\Magento\Framework\Mail\TransportInterface::class);
        $transportMock->expects($this->once())
            ->method('sendMessage')
            ->will($this->returnSelf());
        $this->_transportBuilderMock->expects($this->once())
            ->method('getTransport')
            ->will($this->returnValue($transportMock));

        $this->_model->setData(
            [
                'website_id' => 1,
                'store_id' => 1,
                'email' => 'email@example.com',
                'firstname' => 'FirstName',
                'lastname' => 'LastName',
                'middlename' => 'MiddleName',
                'prefix' => 'Name Prefix',
            ]
        );
        $this->_model->sendNewAccountEmail('registered');
    }

    /**
     * @param $lockExpires
     * @param $expectedResult
     * @dataProvider isCustomerLockedDataProvider
     */
    public function testIsCustomerLocked($lockExpires, $expectedResult)
    {
        $this->_model->setLockExpires($lockExpires);
        $this->assertEquals($expectedResult, $this->_model->isCustomerLocked());
    }

    /**
     * @return array
     */
    public function isCustomerLockedDataProvider()
    {
        return [
            ['lockExpirationDate' => date("F j, Y", strtotime('-1 days')), 'expectedResult' => false],
            ['lockExpirationDate' => date("F j, Y", strtotime('+1 days')), 'expectedResult' => true]
        ];
    }

    /**
     * @param int $customerId
     * @param int $websiteId
     * @param bool $isConfirmationRequired
     * @param bool $expected
     * @dataProvider dataProviderIsConfirmationRequired
     */
    public function testIsConfirmationRequired(
        $customerId,
        $websiteId,
        $isConfirmationRequired,
        $expected
    ) {
        $customerEmail = 'test1@example.com';

        $this->_model->setData('id', $customerId);
        $this->_model->setData('website_id', $websiteId);
        $this->_model->setData('email', $customerEmail);

        $this->accountConfirmation->expects($this->once())
            ->method('isConfirmationRequired')
            ->with($websiteId, $customerId, $customerEmail)
            ->willReturn($isConfirmationRequired);

        $this->assertEquals($expected, $this->_model->isConfirmationRequired());
    }

    /**
     * @return array
     */
    public function dataProviderIsConfirmationRequired()
    {
        return [
            [null, null, false, false],
            [1, 1, true, true],
            [1, null, true, true],
        ];
    }

    public function testUpdateData()
    {
        $customerDataAttributes = [
            'attribute' => 'attribute',
            'test1' => 'test1',
            'test33' => 'test33',
        ];

        $customer = $this->createPartialMock(
            \Magento\Customer\Model\Data\Customer::class,
            [
                'getCustomAttributes',
                'getId',
            ]
        );

        $attribute = $this->createPartialMock(
            \Magento\Framework\Api\AttributeValue::class,
            [
                'getAttributeCode',
                'getValue',
            ]
        );

        $this->dataObjectProcessor->expects($this->once())
            ->method('buildOutputDataArray')
            ->withConsecutive(
                [$customer, \Magento\Customer\Api\Data\CustomerInterface::class]
            )->willReturn($customerDataAttributes);

        $attribute->expects($this->exactly(3))
            ->method('getAttributeCode')
            ->willReturn('test33');

        $attribute->expects($this->exactly(2))
            ->method('getValue')
            ->willReturn('test33');

        $customer->expects($this->once())
            ->method('getCustomAttributes')
            ->willReturn([$attribute->getAttributeCode() => $attribute]);

        $this->_model->updateData($customer);

        foreach ($customerDataAttributes as $key => $value) {
            $expectedResult[strtolower(trim(preg_replace('/([A-Z]|[0-9]+)/', "_$1", $key), '_'))] = $value;
        }

        $expectedResult[$attribute->getAttributeCode()] = $attribute->getValue();

        $this->assertEquals($this->_model->getData(), $expectedResult);
    }

    /**
     * Test for the \Magento\Customer\Model\Customer::getDataModel() method
     */
    public function testGetDataModel()
    {
        $customerId = 1;
        $this->_model->setEntityId($customerId);
        $this->_model->setId($customerId);
        $addressDataModel = $this->getMockForAbstractClass(\Magento\Customer\Api\Data\AddressInterface::class);
        $address = $this->getMockBuilder(\Magento\Customer\Model\Address::class)
            ->disableOriginalConstructor()
            ->setMethods(['setCustomer', 'getDataModel'])
            ->getMock();
        $address->expects($this->atLeastOnce())->method('getDataModel')->willReturn($addressDataModel);
        $addresses = new \ArrayIterator([$address, $address]);
        $addressCollection = $this->getMockBuilder(\Magento\Customer\Model\ResourceModel\Address\Collection::class)
            ->disableOriginalConstructor()
            ->setMethods(['setCustomerFilter', 'addAttributeToSelect', 'getIterator', 'getItems'])
            ->getMock();
        $addressCollection->expects($this->atLeastOnce())->method('setCustomerFilter')->willReturnSelf();
        $addressCollection->expects($this->atLeastOnce())->method('addAttributeToSelect')->willReturnSelf();
        $addressCollection->expects($this->atLeastOnce())->method('getIterator')
            ->willReturn($addresses);
        $addressCollection->expects($this->atLeastOnce())->method('getItems')
            ->willReturn($addresses);
        $this->addressesFactory->expects($this->atLeastOnce())->method('create')->willReturn($addressCollection);
        $customerDataObject = $this->getMockForAbstractClass(\Magento\Customer\Api\Data\CustomerInterface::class);
        $this->customerDataFactory->expects($this->atLeastOnce())->method('create')->willReturn($customerDataObject);
        $this->dataObjectHelper->expects($this->atLeastOnce())->method('populateWithArray')
            ->with($customerDataObject, $this->_model->getData(), \Magento\Customer\Api\Data\CustomerInterface::class)
            ->willReturnSelf();
        $customerDataObject->expects($this->atLeastOnce())->method('setAddresses')
            ->with([$addressDataModel, $addressDataModel])
            ->willReturnSelf();
        $customerDataObject->expects($this->atLeastOnce())->method('setId')->with($customerId)->willReturnSelf();
        $this->_model->getDataModel();
        $this->assertEquals($customerDataObject, $this->_model->getDataModel());
    }

    /**
     * Check getRandomConfirmationKey use cryptographically secure function
     *
     * @return void
     */
    public function testGetRandomConfirmationKey() : void
    {
        $this->mathRandom
            ->expects($this->once())
            ->method('getRandomString')
            ->with(32)
            ->willReturn('random_string');

        $this->_model->getRandomConfirmationKey();
    }
}
