<?php
/**
 * Unit test for customer service layer \Magento\Customer\Model\Customer
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test class for \Magento\Customer\Model\Customer testing
 */
namespace Magento\Customer\Test\Unit\Model;

use Magento\Customer\Model\Customer;
use Magento\Store\Model\ScopeInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CustomerTest extends \PHPUnit_Framework_TestCase
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

    protected function setUp()
    {
        $this->_website = $this->getMock('Magento\Store\Model\Website', [], [], '', false);
        $this->_config = $this->getMock('Magento\Eav\Model\Config', [], [], '', false);
        $this->_attribute = $this->getMock('Magento\Eav\Model\Attribute', [], [], '', false);
        $this->_storeManager = $this->getMock('Magento\Store\Model\StoreManager', [], [], '', false);
        $this->_storetMock = $this->getMock('\Magento\Store\Model\Store', [], [], '', false);
        $this->_scopeConfigMock = $this->getMock('\Magento\Framework\App\Config\ScopeConfigInterface');
        $this->_transportBuilderMock = $this->getMock(
            '\Magento\Framework\Mail\Template\TransportBuilder',
            [],
            [],
            '',
            false
        );
        $this->_transportMock = $this->getMock(
            'Magento\Framework\Mail\TransportInterface',
            [],
            [],
            '',
            false
        );
        $this->attributeFactoryMock = $this->getMock(
            'Magento\Customer\Model\AttributeFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->attributeCustomerMock = $this->getMock(
            'Magento\Customer\Model\Attribute',
            [],
            [],
            '',
            false
        );
        $this->resourceMock = $this->getMock(
            '\Magento\Customer\Model\ResourceModel\Customer', //'\Magento\Framework\DataObject',
            ['getIdFieldName'],
            [],
            '',
            false,
            false
        );
        $this->resourceMock->expects($this->any())
            ->method('getIdFieldName')
            ->will($this->returnValue('id'));
        $this->registryMock = $this->getMock('Magento\Framework\Registry', ['registry'], [], '', false);
        $this->_encryptor = $this->getMock('Magento\Framework\Encryption\EncryptorInterface');
        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->_model = $helper->getObject(
            'Magento\Customer\Model\Customer',
            [
                'storeManager' => $this->_storeManager,
                'config' => $this->_config,
                'transportBuilder' => $this->_transportBuilderMock,
                'scopeConfig' => $this->_scopeConfigMock,
                'encryptor' => $this->_encryptor,
                'attributeFactory' => $this->attributeFactoryMock,
                'registry' => $this->registryMock,
                'resource' => $this->resourceMock,
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
     * @expectedExceptionMessage Please correct the transactional account email type.
     */
    public function testSendNewAccountEmailException()
    {
        $this->_model->sendNewAccountEmail('test');
    }

    public function testSendNewAccountEmailWithoutStoreId()
    {
        $store = $this->getMock('Magento\Store\Model\Store', [], [], '', false);
        $website = $this->getMock('Magento\Store\Model\Website', [], [], '', false);
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
        $transportMock = $this->getMock('Magento\Framework\Mail\TransportInterface', [], [], '', false);
        $transportMock->expects($this->once())
            ->method('sendMessage')
            ->will($this->returnSelf());
        $this->_transportBuilderMock->expects($this->once())
            ->method('getTransport')
            ->will($this->returnValue($transportMock));

        $this->_model->setData([
                'website_id' => 1,
                'store_id'   => 1,
                'email'      => 'email@example.com',
                'firstname'  => 'FirstName',
                'lastname'   => 'LastName',
                'middlename' => 'MiddleName',
                'prefix'     => 'Prefix',
        ]);
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
     * @param string|null $skipConfirmationIfEmail
     * @param bool $expected
     * @dataProvider dataProviderIsConfirmationRequired
     */
    public function testIsConfirmationRequired(
        $customerId,
        $websiteId,
        $skipConfirmationIfEmail,
        $expected
    ) {
        $customerEmail = 'test1@example.com';

        $this->registryMock->expects($this->any())
            ->method('registry')
            ->with('skip_confirmation_if_email')
            ->willReturn($skipConfirmationIfEmail);

        $this->_scopeConfigMock->expects($this->any())
            ->method('getValue')
            ->with(Customer::XML_PATH_IS_CONFIRM, ScopeInterface::SCOPE_WEBSITES, $websiteId)
            ->willReturn($expected);

        $this->_model->setData('id', $customerId);
        $this->_model->setData('website_id', $websiteId);
        $this->_model->setData('email', $customerEmail);

        $this->assertEquals($expected, $this->_model->isConfirmationRequired());
    }

    /**
     * @return array
     */
    public function dataProviderIsConfirmationRequired()
    {
        return [
            [null, null, null, false],
            [1, 1, null, false],
            [1, 1, 'test1@example.com', false],
            [1, 1, 'test2@example.com', true],
            [1, 0, 'test2@example.com', true],
            [1, null, 'test2@example.com', true],
        ];
    }
}
