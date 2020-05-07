<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Contact\Test\Unit\Helper;

use Magento\Contact\Helper\Data;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Helper\View;
use Magento\Customer\Model\Data\Customer;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Magento\Contact\Helper\Data
 */
class DataTest extends TestCase
{
    /**
     * Helper
     *
     * @var Data
     */
    private $helper;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $scopeConfigMock;

    /**
     * @var Session|MockObject
     */
    private $customerSessionMock;

    /**
     * @var View|MockObject
     */
    private $customerViewHelperMock;

    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->scopeConfigMock = $this->getMockForAbstractClass(ScopeConfigInterface::class);

        $contextMock = $this->getMockBuilder(Context::class)
            ->setMethods(['getScopeConfig'])
            ->disableOriginalConstructor()
            ->getMock();
        $contextMock->expects($this->any())
            ->method('getScopeConfig')
            ->willReturn($this->scopeConfigMock);

        $this->customerSessionMock = $this->createMock(Session::class);

        $this->customerViewHelperMock = $this->createMock(View::class);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->helper = $this->objectManagerHelper->getObject(
            Data::class,
            [
                'context' => $contextMock,
                'customerSession' => $this->customerSessionMock,
                'customerViewHelper' => $this->customerViewHelperMock
            ]
        );
    }

    /**
     * Test isEnabled()
     */
    public function testIsEnabled(): void
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->willReturn('1');

        $this->assertIsString($this->helper->isEnabled());
    }

    /**
     * Test if is not enabled
     */
    public function testIsNotEnabled(): void
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->willReturn(null);

        $this->assertNull($this->helper->isEnabled());
    }

    /**
     * Test get user name if not customer loggedin
     */
    public function testGetUserNameNotLoggedIn(): void
    {
        $this->customerSessionMock->expects($this->once())
            ->method('isLoggedIn')
            ->willReturn(false);

        $this->assertEmpty($this->helper->getUserName());
    }

    /**
     * Test get Username from loggedin customer
     */
    public function testGetUserName(): void
    {
        $this->customerSessionMock->expects($this->once())
            ->method('isLoggedIn')
            ->willReturn(true);

        $customerDataObject = $this->getMockBuilder(Customer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->customerSessionMock->expects($this->once())
            ->method('getCustomerDataObject')
            ->willReturn($customerDataObject);

        $this->customerViewHelperMock->expects($this->once())
            ->method('getCustomerName')
            ->willReturn(' customer name ');

        $this->assertEquals('customer name', $this->helper->getUserName());
    }

    /**
     * Test get user email for not loggedin customers
     */
    public function testGetUserEmailNotLoggedIn(): void
    {
        $this->customerSessionMock->expects($this->once())
            ->method('isLoggedIn')
            ->willReturn(false);

        $this->assertEmpty($this->helper->getUserEmail());
    }

    /**
     * Test get user email for loggedin customers
     */
    public function testGetUserEmail(): void
    {
        $this->customerSessionMock->expects($this->once())
            ->method('isLoggedIn')
            ->willReturn(true);

        $customerDataObject = $this->getMockForAbstractClass(CustomerInterface::class);
        $customerDataObject->expects($this->once())
            ->method('getEmail')
            ->willReturn('customer@email.com');

        $this->customerSessionMock->expects($this->once())
            ->method('getCustomerDataObject')
            ->willReturn($customerDataObject);

        $this->assertEquals('customer@email.com', $this->helper->getUserEmail());
    }

    /**
     * Test get post value
     */
    public function testGetPostValue(): void
    {
        $postDataStub = [
            'name' => 'Some Name',
            'email' => 'Some Email'
        ];

        $dataPersistorMock = $this->getMockBuilder(DataPersistorInterface::class)
            ->getMockForAbstractClass();
        $dataPersistorMock->expects($this->once())
            ->method('get')
            ->with('contact_us')
            ->willReturn($postDataStub);
        $dataPersistorMock->expects($this->once())
            ->method('clear')
            ->with('contact_us');

        $this->objectManagerHelper->setBackwardCompatibleProperty($this->helper, 'dataPersistor', $dataPersistorMock);

        $this->assertSame($postDataStub['name'], $this->helper->getPostValue('name'));
        $this->assertSame($postDataStub['email'], $this->helper->getPostValue('email'));
    }
}
