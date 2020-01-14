<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Model\Order\Email\Container;

use \Magento\Sales\Model\Order\Email\Container\OrderIdentity;

class OrderIdentityTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Sales\Model\Order\Email\Container\OrderIdentity
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
        $this->scopeConfigInterfaceMock = $this->createMock(
            \Magento\Framework\App\Config\ScopeConfigInterface::class
        );
        $this->storeManagerMock = $this->createMock(\Magento\Store\Model\StoreManagerInterface::class);

        $this->storeMock = $this->createPartialMock(\Magento\Store\Model\Store::class, ['getStoreId', '__wakeup']);

        $this->storeId = 999999999999;
        $this->storeMock->expects($this->any())
            ->method('getStoreId')
            ->will($this->returnValue($this->storeId));

        $this->identity = new OrderIdentity($this->scopeConfigInterfaceMock, $this->storeManagerMock);
    }

    public function testIsEnabledTrue()
    {
        $this->scopeConfigInterfaceMock->expects($this->once())
            ->method('isSetFlag')
            ->with(
                $this->equalTo(OrderIdentity::XML_PATH_EMAIL_ENABLED),
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
                $this->equalTo(OrderIdentity::XML_PATH_EMAIL_COPY_TO),
                $this->equalTo(\Magento\Store\Model\ScopeInterface::SCOPE_STORE),
                $this->equalTo($this->storeId)
            )
            ->will($this->returnValue('test_value,test_value2'));
        $this->identity->setStore($this->storeMock);
        $result = $this->identity->getEmailCopyTo();
        $this->assertEquals(['test_value', 'test_value2'], $result);
    }

    public function testGetEmailCopyToWithSpaceEmail()
    {
        $this->scopeConfigInterfaceMock->expects($this->once())
            ->method('getValue')
            ->with(
                $this->equalTo(OrderIdentity::XML_PATH_EMAIL_COPY_TO),
                $this->equalTo(\Magento\Store\Model\ScopeInterface::SCOPE_STORE),
                $this->equalTo($this->storeId)
            )
            ->will($this->returnValue('test_value, test_value2'));
        $this->identity->setStore($this->storeMock);
        $result = $this->identity->getEmailCopyTo();
        $this->assertEquals(['test_value', 'test_value2'], $result);
    }

    public function testGetEmailCopyToEmptyResult()
    {
        $this->scopeConfigInterfaceMock->expects($this->once())
            ->method('getValue')
            ->with(
                $this->equalTo(OrderIdentity::XML_PATH_EMAIL_COPY_TO),
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
                $this->equalTo(OrderIdentity::XML_PATH_EMAIL_COPY_METHOD),
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
                $this->equalTo(OrderIdentity::XML_PATH_EMAIL_GUEST_TEMPLATE),
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
                $this->equalTo(OrderIdentity::XML_PATH_EMAIL_TEMPLATE),
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

    public function testSetCustomerName()
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
                $this->equalTo(OrderIdentity::XML_PATH_EMAIL_IDENTITY),
                $this->equalTo(\Magento\Store\Model\ScopeInterface::SCOPE_STORE),
                $this->equalTo($this->storeId)
            )
            ->will($this->returnValue($emailIdentity));

        $this->identity->setStore($this->storeMock);
        $result = $this->identity->getEmailIdentity();
        $this->assertEquals($emailIdentity, $result);
    }
}
