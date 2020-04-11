<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Contact\Test\Unit\Helper;

use Magento\Contact\Helper\Data;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Helper\View;
use Magento\Customer\Model\Data\Customer;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DataTest extends TestCase
{
    /**
     * Helper
     *
     * @var Data
     */
    protected $helper;

    /**
     * Scope config mock
     *
     * @var ScopeConfigInterface|MockObject
     */
    protected $scopeConfigMock;

    /**
     * Customer session mock
     *
     * @var Session|MockObject
     */
    protected $customerSessionMock;

    /**
     * Customer view helper mock
     *
     * @var View|MockObject
     */
    protected $customerViewHelperMock;

    /**
     * @var ObjectManager
     */
    protected $objectManagerHelper;

    protected function setUp(): void
    {
        $this->objectManagerHelper = new ObjectManager($this);
        $className = Data::class;
        $arguments = $this->objectManagerHelper->getConstructArguments($className);
        /**
         * @var Context $context
         */
        $context = $arguments['context'];
        $this->scopeConfigMock = $context->getScopeConfig();
        $this->customerSessionMock = $arguments['customerSession'];
        $this->customerViewHelperMock = $arguments['customerViewHelper'];
        $this->helper = $this->objectManagerHelper->getObject($className, $arguments);
    }

    public function testIsEnabled()
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->willReturn('1');

        $this->assertTrue(is_string($this->helper->isEnabled()));
    }

    public function testIsNotEnabled()
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->willReturn(null);

        $this->assertTrue(null === $this->helper->isEnabled());
    }

    public function testGetUserNameNotLoggedIn()
    {
        $this->customerSessionMock->expects($this->once())
            ->method('isLoggedIn')
            ->willReturn(false);

        $this->assertEmpty($this->helper->getUserName());
    }

    public function testGetUserName()
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

    public function testGetUserEmailNotLoggedIn()
    {
        $this->customerSessionMock->expects($this->once())
            ->method('isLoggedIn')
            ->willReturn(false);

        $this->assertEmpty($this->helper->getUserEmail());
    }

    public function testGetUserEmail()
    {
        $this->customerSessionMock->expects($this->once())
            ->method('isLoggedIn')
            ->willReturn(true);

        $customerDataObject = $this->createMock(CustomerInterface::class);
        $customerDataObject->expects($this->once())
            ->method('getEmail')
            ->willReturn('customer@email.com');

        $this->customerSessionMock->expects($this->once())
            ->method('getCustomerDataObject')
            ->willReturn($customerDataObject);

        $this->assertEquals('customer@email.com', $this->helper->getUserEmail());
    }

    public function testGetPostValue()
    {
        $postData = ['name' => 'Some Name', 'email' => 'Some Email'];

        $dataPersistorMock = $this->getMockBuilder(DataPersistorInterface::class)
            ->getMockForAbstractClass();
        $dataPersistorMock->expects($this->once())
            ->method('get')
            ->with('contact_us')
            ->willReturn($postData);
        $dataPersistorMock->expects($this->once())
            ->method('clear')
            ->with('contact_us');

        $this->objectManagerHelper->setBackwardCompatibleProperty($this->helper, 'dataPersistor', $dataPersistorMock);

        $this->assertSame($postData['name'], $this->helper->getPostValue('name'));
        $this->assertSame($postData['email'], $this->helper->getPostValue('email'));
    }
}
