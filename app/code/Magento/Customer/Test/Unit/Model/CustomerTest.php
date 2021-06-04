<?php declare(strict_types=1);
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

use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Customer\Model\AccountConfirmation;
use Magento\Customer\Model\Address as AddressModel;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\ResourceModel\Address\Collection as AddressCollection;
use Magento\Customer\Model\ResourceModel\Address\CollectionFactory as AddressCollectionFactory;
use Magento\Customer\Model\ResourceModel\Customer as CustomerResourceModel;
use Magento\Eav\Model\Attribute;
use Magento\Eav\Model\Config;
use Magento\Framework\Api\AttributeValue;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Mail\TransportInterface;
use Magento\Framework\Math\Random;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\Registry;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManager;
use Magento\Store\Model\Website;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class CustomerTest extends TestCase
{
    /** @var Customer */
    protected $_model;

    /** @var Website|MockObject */
    protected $_website;

    /** @var StoreManager|MockObject */
    protected $_storeManager;

    /** @var Config|MockObject */
    protected $_config;

    /** @var Attribute|MockObject */
    protected $_attribute;

    /** @var ScopeConfigInterface|MockObject */
    protected $_scopeConfigMock;

    /** @var TransportBuilder|MockObject */
    protected $_transportBuilderMock;

    /** @var TransportInterface|MockObject */
    protected $_transportMock;

    /** @var EncryptorInterface|MockObject */
    protected $_encryptor;

    /** @var \Magento\Customer\Model\AttributeFactory|MockObject */
    protected $attributeFactoryMock;

    /** @var  \Magento\Customer\Model\Attribute|MockObject */
    protected $attributeCustomerMock;

    /** @var  Registry|MockObject */
    protected $registryMock;

    /** @var CustomerResourceModel|MockObject */
    protected $resourceMock;

    /**
     * @var DataObjectProcessor|MockObject
     */
    private $dataObjectProcessor;

    /**
     * @var AccountConfirmation|MockObject
     */
    private $accountConfirmation;

    /**
     * @var AddressCollectionFactory|MockObject
     */
    private $addressesFactory;

    /**
     * @var CustomerInterfaceFactory|MockObject
     */
    private $customerDataFactory;

    /**
     * @var DataObjectHelper|MockObject
     */
    private $dataObjectHelper;

    /**
     * @var Random|MockObject
     */
    private $mathRandom;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->_website = $this->createMock(Website::class);
        $this->_config = $this->createMock(Config::class);
        $this->_attribute = $this->createMock(Attribute::class);
        $this->_storeManager = $this->createMock(StoreManager::class);
        $this->_scopeConfigMock = $this->getMockForAbstractClass(ScopeConfigInterface::class);
        $this->_transportBuilderMock = $this->createMock(TransportBuilder::class);
        $this->_transportMock = $this->getMockForAbstractClass(TransportInterface::class);
        $this->attributeFactoryMock = $this->createPartialMock(
            \Magento\Customer\Model\AttributeFactory::class,
            ['create']
        );
        $this->attributeCustomerMock = $this->createMock(\Magento\Customer\Model\Attribute::class);
        $this->resourceMock = $this->createPartialMock(
            CustomerResourceModel::class, // \Magento\Framework\DataObject::class,
            ['getIdFieldName']
        );

        $this->dataObjectProcessor = $this->createPartialMock(
            DataObjectProcessor::class,
            ['buildOutputDataArray']
        );

        $this->resourceMock->expects($this->any())
            ->method('getIdFieldName')
            ->willReturn('id');
        $this->registryMock = $this->createPartialMock(Registry::class, ['registry']);
        $this->_encryptor = $this->getMockForAbstractClass(EncryptorInterface::class);
        $helper = new ObjectManager($this);
        $this->accountConfirmation = $this->createMock(AccountConfirmation::class);
        $this->addressesFactory = $this->getMockBuilder(AddressCollectionFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->customerDataFactory = $this->getMockBuilder(CustomerInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->dataObjectHelper = $this->getMockBuilder(DataObjectHelper::class)
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
        )->willReturn(
            'hash'
        );
        $this->assertEquals('hash', $this->_model->hashPassword('password', 'salt'));
    }

    public function testSendNewAccountEmailException()
    {
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('The transactional account email type is incorrect. Verify and try again.');

        $this->_model->sendNewAccountEmail('test');
    }

    public function testSendNewAccountEmailWithoutStoreId()
    {
        $store = $this->createMock(Store::class);
        $website = $this->createMock(Website::class);
        $website->expects($this->once())
            ->method('getStoreIds')
            ->willReturn([1, 2, 3, 4]);
        $this->_storeManager->expects($this->once())
            ->method('getWebsite')
            ->with(1)
            ->willReturn($website);
        $this->_storeManager->expects($this->once())
            ->method('getStore')
            ->with(1)
            ->willReturn($store);

        $this->_config->expects($this->exactly(3))
            ->method('getAttribute')
            ->willReturn($this->_attribute);

        $this->_attribute->expects($this->exactly(3))
            ->method('getIsVisible')
            ->willReturn(true);

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
                ->willReturnSelf();
        }
        $transportMock = $this->getMockForAbstractClass(TransportInterface::class);
        $transportMock->expects($this->once())
            ->method('sendMessage')
            ->willReturnSelf();
        $this->_transportBuilderMock->expects($this->once())
            ->method('getTransport')
            ->willReturn($transportMock);

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
            AttributeValue::class,
            [
                'getAttributeCode',
                'getValue',
            ]
        );

        $this->dataObjectProcessor->expects($this->once())
            ->method('buildOutputDataArray')
            ->withConsecutive(
                [$customer, CustomerInterface::class]
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
        $addressDataModel = $this->getMockForAbstractClass(AddressInterface::class);
        $address = $this->getMockBuilder(AddressModel::class)
            ->disableOriginalConstructor()
            ->setMethods(['setCustomer', 'getDataModel'])
            ->getMock();
        $address->expects($this->atLeastOnce())->method('getDataModel')->willReturn($addressDataModel);
        $addresses = new \ArrayIterator([$address, $address]);
        $addressCollection = $this->getMockBuilder(AddressCollection::class)
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
        $customerDataObject = $this->getMockForAbstractClass(CustomerInterface::class);
        $this->customerDataFactory->expects($this->atLeastOnce())->method('create')->willReturn($customerDataObject);
        $this->dataObjectHelper->expects($this->atLeastOnce())->method('populateWithArray')
            ->with($customerDataObject, $this->_model->getData(), CustomerInterface::class)
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
