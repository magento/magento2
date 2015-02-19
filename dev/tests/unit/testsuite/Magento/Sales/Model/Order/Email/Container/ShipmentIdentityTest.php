<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order\Email\Container;

class ShipmentIdentityTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Model\Order\Email\Container\ShipmentIdentity
     */
    protected $identity;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $scopeConfigInterfaceMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeMock;

    protected $storeId;

    protected function setUp()
    {
        $this->scopeConfigInterfaceMock = $this->getMockForAbstractClass(
            '\Magento\Framework\App\Config\ScopeConfigInterface'
        );
        $this->storeManagerMock = $this->getMock(
            '\Magento\Store\Model\Storage\DefaultStorage',
            [],
            [],
            '',
            false
        );

        $this->storeMock = $this->getMock(
            '\Magento\Store\Model\Store',
            ['getStoreId', '__wakeup'],
            [],
            '',
            false
        );

        $this->storeId = 999999999999;
        $this->storeMock->expects($this->any())
            ->method('getStoreId')
            ->will($this->returnValue($this->storeId));

        $this->identity = new ShipmentIdentity($this->scopeConfigInterfaceMock, $this->storeManagerMock);
    }

    public function testIsEnabledTrue()
    {
        $this->scopeConfigInterfaceMock->expects($this->once())
            ->method('isSetFlag')
            ->with(
                $this->equalTo(ShipmentIdentity::XML_PATH_EMAIL_ENABLED),
                $this->equalTo(\Magento\Store\Model\ScopeInterface::SCOPE_STORE),
                $this->equalTo($this->storeId)
            )
            ->will($this->returnValue(true));
        $this->identity->setStore($this->storeMock);
        $result = $this->identity->isEnabled();
        $this->assertTrue($result);
    }

    public function testGetEmailCopyTo()
    {
        $this->scopeConfigInterfaceMock->expects($this->once())
            ->method('getValue')
            ->with(
                $this->equalTo(ShipmentIdentity::XML_PATH_EMAIL_COPY_TO),
                $this->equalTo(\Magento\Store\Model\ScopeInterface::SCOPE_STORE),
                $this->equalTo($this->storeId)
            )
            ->will($this->returnValue('test_value,test_value2'));
        $this->identity->setStore($this->storeMock);
        $result = $this->identity->getEmailCopyTo();
        $this->assertEquals(['test_value', 'test_value2'], $result);
    }

    public function testGetEmailCopyToEmptyResult()
    {
        $this->scopeConfigInterfaceMock->expects($this->once())
            ->method('getValue')
            ->with(
                $this->equalTo(ShipmentIdentity::XML_PATH_EMAIL_COPY_TO),
                $this->equalTo(\Magento\Store\Model\ScopeInterface::SCOPE_STORE),
                $this->equalTo($this->storeId)
            )
            ->will($this->returnValue(null));
        $this->identity->setStore($this->storeMock);
        $result = $this->identity->getEmailCopyTo();
        $this->assertFalse($result);
    }

    public function testCopyMethod()
    {
        $this->scopeConfigInterfaceMock->expects($this->once())
            ->method('getValue')
            ->with(
                $this->equalTo(ShipmentIdentity::XML_PATH_EMAIL_COPY_METHOD),
                $this->equalTo(\Magento\Store\Model\ScopeInterface::SCOPE_STORE),
                $this->equalTo($this->storeId)
            )
            ->will($this->returnValue('copy_method'));

        $this->identity->setStore($this->storeMock);
        $result = $this->identity->getCopyMethod();
        $this->assertEquals('copy_method', $result);
    }

    public function testGuestTemplateId()
    {
        $this->scopeConfigInterfaceMock->expects($this->once())
            ->method('getValue')
            ->with(
                $this->equalTo(ShipmentIdentity::XML_PATH_EMAIL_GUEST_TEMPLATE),
                $this->equalTo(\Magento\Store\Model\ScopeInterface::SCOPE_STORE),
                $this->equalTo($this->storeId)
            )
            ->will($this->returnValue('template_id'));

        $this->identity->setStore($this->storeMock);
        $result = $this->identity->getGuestTemplateId();
        $this->assertEquals('template_id', $result);
    }

    public function testTemplateId()
    {
        $this->scopeConfigInterfaceMock->expects($this->once())
            ->method('getValue')
            ->with(
                $this->equalTo(ShipmentIdentity::XML_PATH_EMAIL_TEMPLATE),
                $this->equalTo(\Magento\Store\Model\ScopeInterface::SCOPE_STORE),
                $this->equalTo($this->storeId)
            )
            ->will($this->returnValue('template_id'));

        $this->identity->setStore($this->storeMock);
        $result = $this->identity->getTemplateId();
        $this->assertEquals('template_id', $result);
    }

    public function testSetStore()
    {
        $this->identity->setStore($this->storeMock);
        $result = $this->identity->getStore();
        $this->assertEquals($this->storeMock, $result);
    }

    public function testGetStoreFromStoreManager()
    {
        $this->storeManagerMock->expects($this->once())
            ->method('getStore')
            ->will($this->returnValue($this->storeMock));
        $result = $this->identity->getStore();
        $this->assertEquals($this->storeMock, $result);
    }

    public function testSetCustomerEmail()
    {
        $this->identity->setCustomerEmail('email');
        $result = $this->identity->getCustomerEmail();
        $this->assertEquals('email', $result);
    }

    public function testSetCusomerName()
    {
        $this->identity->setCustomerName('name');
        $result = $this->identity->getCustomerName();
        $this->assertEquals('name', $result);
    }

    public function testGetEmailIdentity()
    {
        $emailIdentity = 'test@example.com';
        $this->scopeConfigInterfaceMock->expects($this->once())
            ->method('getValue')
            ->with(
                $this->equalTo(ShipmentIdentity::XML_PATH_EMAIL_IDENTITY),
                $this->equalTo(\Magento\Store\Model\ScopeInterface::SCOPE_STORE),
                $this->equalTo($this->storeId)
            )
            ->will($this->returnValue($emailIdentity));

        $this->identity->setStore($this->storeMock);
        $result = $this->identity->getEmailIdentity();
        $this->assertEquals($emailIdentity, $result);
    }
}
