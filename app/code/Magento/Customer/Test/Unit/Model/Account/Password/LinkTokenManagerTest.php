<?php
/**
 * @package  Magento\Customer
 * @author Bartosz Herba <bherba@divante.pl>
 * @copyright 2017 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Magento\Customer\Test\Unit\Model\Account\Password;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\LinkTokenManagerInterface;
use Magento\Customer\Model\Account\Password\LinkTokenManager;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Customer\Model\ResourceModel\Customer;
use Magento\Framework\Intl\DateTimeFactory;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Class LinkTokenManagerTest
 */
class LinkTokenManagerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Customer\Model\Data\Customer|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockCustomerData;

    /**
     * @var LinkTokenManagerInterface
     */
    private $linkTokenManager;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var CustomerRegistry|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockCustomerRegistry;

    /**
     * @var DateTimeFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockDateTimeFactory;

    /**
     * @var DateTime|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockDateTime;

    /**
     * @var string
     */
    private $passwordLinkToken = 'token';

    /**
     * @var Customer|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockCustomerResource;

    public function setUp()
    {
        $this->objectManager = new ObjectManager($this);

        $this->mockCustomerRegistry = $this->getMockBuilder(CustomerRegistry::class)
            ->disableOriginalConstructor()
            ->setMethods(['retrieve'])
            ->getMock();

        $this->mockDateTimeFactory = $this->getMockBuilder(DateTimeFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->mockDateTime = $this->getMockBuilder(DateTime::class)
            ->disableOriginalConstructor()
            ->setMethods(['format'])
            ->getMock();

        $this->mockCustomerResource = $this->getMockBuilder(Customer::class)
            ->disableOriginalConstructor()
            ->setMethods(['save'])
            ->getMock();

        /** @var CustomerInterface $mockCustomerData */
        $this->mockCustomerData = $this->getMockBuilder(\Magento\Customer\Model\Data\Customer::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId'])
            ->getMock();

        $this->linkTokenManager = $this->objectManager->getObject(LinkTokenManager::class, [
            'customerRegistry' => $this->mockCustomerRegistry,
            'dateTimeFactory'  => $this->mockDateTimeFactory,
            'customerResource' => $this->mockCustomerResource,
        ]);
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage Invalid value of "" provided for the password reset token field.
     */
    public function testEmptyTokenThrowsException()
    {
        $this->linkTokenManager->changeToken($this->mockCustomerData, '');
    }

    /**
     * Test change method
     */
    public function testChangeRpToken()
    {
        $customerId = 1;

        $this->mockCustomerData->expects($this->once())
            ->method('getId')
            ->willReturn($customerId);

        $mockCustomer = $this->getMockBuilder(\Magento\Customer\Model\Customer::class)
            ->disableOriginalConstructor()
            ->setMethods(['setRpToken', 'setRpTokenCreatedAt'])
            ->getMock();

        $this->mockCustomerRegistry->expects($this->once())
            ->method('retrieve')
            ->with($customerId)
            ->willReturn($mockCustomer);

        $mockCustomer->expects($this->once())
            ->method('setRpToken')
            ->with($this->passwordLinkToken);

        $this->mockDateTimeFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->mockDateTime);

        $dateFormat = 'some-format';

        $this->mockDateTime->expects($this->once())
            ->method('format')
            ->with(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT)
            ->willReturn($dateFormat);

        $mockCustomer->expects($this->once())
            ->method('setRpTokenCreatedAt')
            ->with($dateFormat);

        $this->assertTrue($this->linkTokenManager->changeToken($this->mockCustomerData, $this->passwordLinkToken));
    }
}
