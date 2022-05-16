<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\CustomerGraphQl\Test\Unit\Model\Resolver;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\CustomerGraphQl\Model\Resolver\IsSubscribed;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\GraphQl\Model\Query\ContextExtensionInterface;
use Magento\GraphQl\Model\Query\ContextInterface;
use Magento\Newsletter\Model\Subscriber;
use Magento\Newsletter\Model\SubscriberFactory;
use Magento\Store\Api\Data\StoreInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test class for \Magento\CustomerGraphQl\Model\Resolver\IsSubscribed
 */
class IsSubscribedTest extends TestCase
{
    /**
     * Object Manager Instance
     *
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * Testable Object
     *
     * @var IsSubscribed
     */
    private $resolver;

    /**
     * @var ContextInterface|MockObject
     */
    private $contextMock;

    /**
     * @var ContextExtensionInterface|MockObject
     */
    private $contextExtensionMock;

    /**
     * @var CustomerInterface|MockObject
     */
    private $customerMock;

    /**
     * @var SubscriberFactory|MockObject
     */
    private $subscriberFactory;

    /**
     * @var Subscriber|MockObject
     */
    private $subscriberMock;

    /**
     * @var StoreInterface|MockObject
     */
    private $storeMock;

    /**
     * @var Field|MockObject
     */
    private $fieldMock;

    /**
     * @var ResolveInfo|MockObject
     */
    private $resolveInfoMock;

    /**
     * @var array
     */
    private array $valueMock = [];

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);

        $this->contextMock = $this->getMockBuilder(ContextInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->contextExtensionMock = $this->getMockBuilder(ContextExtensionInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getStore'])
            ->getMockForAbstractClass();

        $this->customerMock = $this->getMockBuilder(CustomerInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId'])
            ->getMockForAbstractClass();

        $this->storeMock = $this->getMockBuilder(StoreInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getWebsiteId'])
            ->getMockForAbstractClass();

        $this->fieldMock = $this->getMockBuilder(Field::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->subscriberFactory = $this->createMock(SubscriberFactory::class);
        $this->subscriberMock = $this->createMock(Subscriber::class);

        $this->resolveInfoMock = $this->getMockBuilder(ResolveInfo::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->resolver = $this->objectManager->getObject(
            IsSubscribed::class,
            [
                'subscriberFactory' => $this->subscriberFactory
            ]
        );
    }

    /**
     * Test customer is subscribed
     */
    public function testCustomerIsSubscribed()
    {
        $subscriber = $this->createMock(Subscriber::class);
        $this->valueMock = ['model' => $this->customerMock];
        $this->contextMock
            ->expects($this->once())
            ->method('getExtensionAttributes')
            ->willReturn($this->contextExtensionMock);

        $this->contextExtensionMock
            ->expects($this->once())
            ->method('getStore')
            ->willReturn($this->storeMock);

        $this->customerMock->expects($this->once())
            ->method('getId')
            ->willReturn(1);

        $this->storeMock->expects($this->any())
            ->method('getWebsiteId')
            ->willReturn(1);

        $this->assertNotNull($this->storeMock->getWebsiteId());

        $this->subscriberFactory->expects($this->once())
            ->method('create')
            ->willReturn($subscriber);

        $subscriber->expects($this->once())
            ->method('loadByCustomer')
            ->willReturn($this->subscriberMock);

        $this->subscriberMock->expects($this->once())
            ->method('isSubscribed')
            ->willReturn(true);

        $this->assertEquals(
            true,
            $this->resolver->resolve(
                $this->fieldMock,
                $this->contextMock,
                $this->resolveInfoMock,
                $this->valueMock
            )
        );
    }

    /**
     * Test subscription status will return false if store not found
     */
    public function testCustomerIsSubscribedWithoutStore()
    {
        $this->valueMock = ['model' => $this->customerMock];
        $this->contextMock
            ->expects($this->once())
            ->method('getExtensionAttributes')
            ->willReturn($this->contextExtensionMock);

        $this->assertEquals(
            false,
            $this->resolver->resolve(
                $this->fieldMock,
                $this->contextMock,
                $this->resolveInfoMock,
                $this->valueMock
            )
        );
    }
}
