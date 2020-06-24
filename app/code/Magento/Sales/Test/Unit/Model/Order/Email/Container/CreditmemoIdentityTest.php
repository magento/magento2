<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\Order\Email\Container;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Sales\Model\Order\Email\Container\CreditmemoIdentity;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CreditmemoIdentityTest extends TestCase
{
    /**
     * @var CreditmemoIdentity
     */
    protected $identity;

    /**
     * @var MockObject
     */
    protected $storeManagerMock;

    /**
     * @var MockObject
     */
    protected $scopeConfigInterfaceMock;

    /**
     * @var MockObject
     */
    protected $storeMock;

    protected $storeId;

    protected function setUp(): void
    {
        $this->scopeConfigInterfaceMock = $this->getMockForAbstractClass(
            ScopeConfigInterface::class
        );
        $this->storeManagerMock = $this->getMockForAbstractClass(StoreManagerInterface::class);

        $this->storeMock = $this->getMockBuilder(Store::class)
            ->addMethods(['getStoreId'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->storeId = 999999999999;
        $this->storeMock->expects($this->any())
            ->method('getStoreId')
            ->willReturn($this->storeId);

        $this->identity = new CreditmemoIdentity($this->scopeConfigInterfaceMock, $this->storeManagerMock);
    }

    public function testIsEnabledTrue()
    {
        $this->scopeConfigInterfaceMock->expects($this->once())
            ->method('isSetFlag')
            ->with(
                CreditmemoIdentity::XML_PATH_EMAIL_ENABLED,
                ScopeInterface::SCOPE_STORE,
                $this->storeId
            )
            ->willReturn(true);
        $this->identity->setStore($this->storeMock);
        $result = $this->identity->isEnabled();
        $this->assertTrue($result);
    }

    public function testGetEmailCopyTo()
    {
        $this->scopeConfigInterfaceMock->expects($this->once())
            ->method('getValue')
            ->with(
                CreditmemoIdentity::XML_PATH_EMAIL_COPY_TO,
                ScopeInterface::SCOPE_STORE,
                $this->storeId
            )
            ->willReturn('test_value,test_value2');
        $this->identity->setStore($this->storeMock);
        $result = $this->identity->getEmailCopyTo();
        $this->assertEquals(['test_value', 'test_value2'], $result);
    }

    public function testGetEmailCopyToWithSpaceEmail()
    {
        $this->scopeConfigInterfaceMock->expects($this->once())
            ->method('getValue')
            ->with(
                CreditmemoIdentity::XML_PATH_EMAIL_COPY_TO,
                ScopeInterface::SCOPE_STORE,
                $this->storeId
            )
            ->willReturn('test_value, test_value2');
        $this->identity->setStore($this->storeMock);
        $result = $this->identity->getEmailCopyTo();
        $this->assertEquals(['test_value', 'test_value2'], $result);
    }

    public function testGetEmailCopyToEmptyResult()
    {
        $this->scopeConfigInterfaceMock->expects($this->once())
            ->method('getValue')
            ->with(
                CreditmemoIdentity::XML_PATH_EMAIL_COPY_TO,
                ScopeInterface::SCOPE_STORE,
                $this->storeId
            )
            ->willReturn(null);
        $this->identity->setStore($this->storeMock);
        $result = $this->identity->getEmailCopyTo();
        $this->assertFalse($result);
    }

    public function testCopyMethod()
    {
        $this->scopeConfigInterfaceMock->expects($this->once())
            ->method('getValue')
            ->with(
                CreditmemoIdentity::XML_PATH_EMAIL_COPY_METHOD,
                ScopeInterface::SCOPE_STORE,
                $this->storeId
            )
            ->willReturn('copy_method');

        $this->identity->setStore($this->storeMock);
        $result = $this->identity->getCopyMethod();
        $this->assertEquals('copy_method', $result);
    }

    public function testGuestTemplateId()
    {
        $this->scopeConfigInterfaceMock->expects($this->once())
            ->method('getValue')
            ->with(
                CreditmemoIdentity::XML_PATH_EMAIL_GUEST_TEMPLATE,
                ScopeInterface::SCOPE_STORE,
                $this->storeId
            )
            ->willReturn('template_id');

        $this->identity->setStore($this->storeMock);
        $result = $this->identity->getGuestTemplateId();
        $this->assertEquals('template_id', $result);
    }

    public function testTemplateId()
    {
        $this->scopeConfigInterfaceMock->expects($this->once())
            ->method('getValue')
            ->with(
                CreditmemoIdentity::XML_PATH_EMAIL_TEMPLATE,
                ScopeInterface::SCOPE_STORE,
                $this->storeId
            )
            ->willReturn('template_id');

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
            ->willReturn($this->storeMock);
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
                CreditmemoIdentity::XML_PATH_EMAIL_IDENTITY,
                ScopeInterface::SCOPE_STORE,
                $this->storeId
            )
            ->willReturn($emailIdentity);

        $this->identity->setStore($this->storeMock);
        $result = $this->identity->getEmailIdentity();
        $this->assertEquals($emailIdentity, $result);
    }
}
