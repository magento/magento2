<?php
/**
 * Unit test for converter \Magento\Customer\Model\Converter
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Customer\Model;

use Magento\Customer\Service\V1\Data\Eav\AttributeMetadata;
use Magento\Customer\Service\V1\Data\CustomerBuilder;
use Magento\Customer\Service\V1\CustomerMetadataServiceInterface;
use Magento\Framework\Service\Data\AttributeValueBuilder;

class ConverterTest extends \PHPUnit_Framework_TestCase
{
    /** @var  \PHPUnit_Framework_MockObject_MockObject | AttributeMetadata */
    private $_attributeMetadata;

    /** @var  \PHPUnit_Framework_MockObject_MockObject | CustomerMetadataServiceInterface */
    private $_metadataService;

    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $_objectManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Framework\StoreManagerInterface
     */
    protected $storeManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Customer\Service\V1\Data\CustomerBuilder
     */
    protected $customerBuilderMock;

    public function setUp()
    {
        $this->_objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);

        $this->_metadataService = $this->getMockForAbstractClass(
            'Magento\Customer\Service\V1\CustomerMetadataServiceInterface',
            array(),
            '',
            false
        );

        $this->_metadataService->expects(
            $this->any()
        )->method(
            'getAttributeMetadata'
        )->will(
            $this->returnValue($this->_attributeMetadata)
        );

        $this->_metadataService->expects(
            $this->any()
        )->method(
            'getCustomAttributesMetadata'
        )->will(
            $this->returnValue(array())
        );

        $this->_attributeMetadata = $this->getMock(
            'Magento\Customer\Service\V1\Data\Eav\AttributeMetadata',
            array(),
            array(),
            '',
            false
        );

        $this->customerBuilderMock = $this->getMock(
            'Magento\Customer\Service\V1\Data\CustomerBuilder',
            array(),
            array(),
            '',
            false
        );
        $this->customerFactoryMock = $this->getMock(
            'Magento\Customer\Model\CustomerFactory',
            array('create'),
            array(),
            '',
            false
        );
        $this->storeManagerMock = $this->getMock(
            'Magento\Framework\StoreManagerInterface',
            array(),
            array(),
            '',
            false
        );
    }

    public function testCreateCustomerFromModel()
    {
        $customerModelMock = $this->getMockBuilder(
            'Magento\Customer\Model\Customer'
        )->disableOriginalConstructor()->setMethods(
            array('getId', 'getFirstname', 'getLastname', 'getEmail', 'getAttributes', 'getData', '__wakeup')
        )->getMock();

        $attributeModelMock = $this->getMockBuilder(
            '\Magento\Customer\Model\Attribute'
        )->disableOriginalConstructor()->getMock();

        $attributeModelMock->expects(
            $this->at(0)
        )->method(
            'getAttributeCode'
        )->will(
            $this->returnValue('attribute_code')
        );

        $attributeModelMock->expects(
            $this->at(1)
        )->method(
            'getAttributeCode'
        )->will(
            $this->returnValue('attribute_code2')
        );

        $attributeModelMock->expects(
            $this->at(2)
        )->method(
            'getAttributeCode'
        )->will(
            $this->returnValue('attribute_code3')
        );

        $this->_mockReturnValue(
            $customerModelMock,
            array(
                'getId' => 1,
                'getFirstname' => 'Tess',
                'getLastname' => 'Tester',
                'getEmail' => 'ttester@example.com',
                'getAttributes' => array($attributeModelMock, $attributeModelMock, $attributeModelMock)
            )
        );

        $map = array(
            array('attribute_code', null, 'attributeValue'),
            array('attribute_code2', null, 'attributeValue2'),
            array('attribute_code3', null, null)
        );
        $customerModelMock->expects($this->any())->method('getData')->will($this->returnValueMap($map));

        $customerBuilder = $this->_objectManager->getObject(
            'Magento\Customer\Service\V1\Data\CustomerBuilder',
            ['metadataService' => $this->_metadataService]
        );

        $customerFactory = $this->getMockBuilder(
            'Magento\Customer\Model\CustomerFactory'
        )->disableOriginalConstructor()->getMock();

        $converter = new Converter($customerBuilder, $customerFactory, $this->storeManagerMock);
        $customerDataObject = $converter->createCustomerFromModel($customerModelMock);

        $customerBuilder = $this->_objectManager->getObject(
            'Magento\Customer\Service\V1\Data\CustomerBuilder',
            ['metadataService' => $this->_metadataService]
        );

        $customerData = array(
            'firstname' => 'Tess',
            'email' => 'ttester@example.com',
            'lastname' => 'Tester',
            'id' => 1,
            'attribute_code' => 'attributeValue',
            'attribute_code2' => 'attributeValue2'
        );
        // There will be no attribute_code3: it has a value of null, so the converter will drop it
        $customerBuilder->populateWithArray($customerData);
        $expectedCustomerData = $customerBuilder->create();

        $this->assertEquals($expectedCustomerData, $customerDataObject);
    }

    protected function prepareGetCustomerModel($customerId)
    {
        $customerMock = $this->getMock('Magento\Customer\Model\Customer', array(), array(), '', false);
        $customerMock->expects($this->once())
            ->method('load')
            ->with($customerId)
            ->will($this->returnSelf());
        $customerMock->expects($this->once())
            ->method('getId')
            ->will($this->returnValue($customerId));

        $this->customerFactoryMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($customerMock));

        $converter = new Converter($this->customerBuilderMock, $this->customerFactoryMock, $this->storeManagerMock);
        return $converter;
    }

    public function testGetCustomerModel()
    {
        $customerId = 1;
        $converter = $this->prepareGetCustomerModel($customerId);
        $this->assertInstanceOf('Magento\Customer\Model\Customer', $converter->getCustomerModel($customerId));
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage No such entity with customerId
     */
    public function testGetCustomerModelException()
    {
        $customerId = 0;
        $converter = $this->prepareGetCustomerModel($customerId);
        $this->assertInstanceOf('Magento\Customer\Model\Customer', $converter->getCustomerModel($customerId));
    }

    /**
     * @param $websiteId
     * @param $customerEmail
     * @param $customerId
     */
    protected function prepareGetCustomerModelByEmail($websiteId, $customerEmail, $customerId)
    {
        $customerMock = $this->getMock(
            'Magento\Customer\Model\Customer',
            array('setWebsiteId', 'loadByEmail', 'getId', '__wakeup'),
            array(),
            '',
            false
        );
        $customerMock->expects($this->once())
            ->method('setWebsiteId')
            ->with($websiteId)
            ->will($this->returnSelf());
        $customerMock->expects($this->once())
            ->method('loadByEmail')
            ->with($customerEmail)
            ->will($this->returnSelf());
        $customerMock->expects($this->once())
            ->method('getId')
            ->will($this->returnValue($customerId));

        $this->customerFactoryMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($customerMock));
    }

    public function testGetCustomerModelByEmail()
    {
        $customerId = 1;
        $websiteId = 1;
        $customerEmail = 'test@example.com';
        $this->prepareGetCustomerModelByEmail($websiteId, $customerEmail, $customerId);

        $storeMock = $this->getMock('Magento\Store\Model\Store', array(), array(), '', false);
        $storeMock->expects($this->once())
            ->method('getWebsiteId')
            ->will($this->returnValue($websiteId));

        $this->storeManagerMock->expects($this->once())
            ->method('getDefaultStoreView')
            ->will($this->returnValue($storeMock));

        $converter = new Converter($this->customerBuilderMock, $this->customerFactoryMock, $this->storeManagerMock);
        $this->assertInstanceOf(
            'Magento\Customer\Model\Customer',
            $converter->getCustomerModelByEmail('test@example.com')
        );
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage No such entity with email
     */
    public function testGetCustomerModelByEmailException()
    {
        $customerId = 0;
        $websiteId = 1;
        $customerEmail = 'test@example.com';
        $this->prepareGetCustomerModelByEmail($websiteId, $customerEmail, $customerId);

        $this->storeManagerMock->expects($this->never())->method('getDefaultStoreView');

        $converter = new Converter($this->customerBuilderMock, $this->customerFactoryMock, $this->storeManagerMock);
        $this->assertInstanceOf(
            'Magento\Customer\Model\Customer',
            $converter->getCustomerModelByEmail('test@example.com', $websiteId)
        );
    }

    /**
     * @param \PHPUnit_Framework_MockObject_MockObject $mock
     * @param array $valueMap
     */
    private function _mockReturnValue(\PHPUnit_Framework_MockObject_MockObject $mock, $valueMap)
    {
        foreach ($valueMap as $method => $value) {
            $mock->expects($this->any())->method($method)->will($this->returnValue($value));
        }
    }
}
