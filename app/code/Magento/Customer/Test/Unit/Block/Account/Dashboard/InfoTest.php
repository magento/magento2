<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Block\Account\Dashboard;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Block\Account\Dashboard\Info;
use Magento\Customer\Block\Form\Register;
use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Customer\Helper\View;
use Magento\Customer\Model\Session;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\View\LayoutInterface;
use Magento\Newsletter\Model\Subscriber;
use Magento\Newsletter\Model\SubscriberFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test class for \Magento\Customer\Block\Account\Dashboard\Info.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class InfoTest extends TestCase
{
    /** Constant values used for testing */
    const CUSTOMER_ID = 1;

    const CHANGE_PASSWORD_URL = 'http://localhost/index.php/account/edit/changepass/1';

    const EMAIL_ADDRESS = 'john.doe@example.com';

    /** @var MockObject|Context */
    private $_context;

    /** @var MockObject|Session */
    private $_customerSession;

    /** @var MockObject|CustomerInterface */
    private $_customer;

    /**
     * @var MockObject|View
     */
    private $_helperView;

    /** @var MockObject|Subscriber */
    private $_subscriber;

    /** @var MockObject|SubscriberFactory */
    private $_subscriberFactory;

    /** @var MockObject|Register */
    private $_formRegister;

    /** @var Info */
    private $_block;

    /**
     * @var MockObject|CurrentCustomer
     */
    protected $currentCustomer;

    protected function setUp(): void
    {
        $this->currentCustomer = $this->createMock(CurrentCustomer::class);

        $urlBuilder = $this->getMockForAbstractClass(UrlInterface::class, [], '', false);
        $urlBuilder->expects($this->any())->method('getUrl')->willReturn(self::CHANGE_PASSWORD_URL);

        $layout = $this->getMockForAbstractClass(LayoutInterface::class, [], '', false);
        $this->_formRegister = $this->createMock(Register::class);
        $layout->expects($this->any())
            ->method('getBlockSingleton')
            ->with(Register::class)
            ->willReturn($this->_formRegister);

        $this->_context = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->_context->expects($this->once())->method('getUrlBuilder')->willReturn($urlBuilder);
        $this->_context->expects($this->once())->method('getLayout')->willReturn($layout);

        $this->_customerSession = $this->createMock(Session::class);
        $this->_customerSession->expects($this->any())->method('getId')->willReturn(self::CUSTOMER_ID);

        $this->_customer = $this->getMockForAbstractClass(CustomerInterface::class);
        $this->_customer->expects($this->any())->method('getEmail')->willReturn(self::EMAIL_ADDRESS);
        $this->_helperView = $this->getMockBuilder(
            View::class
        )->disableOriginalConstructor()
            ->getMock();
        $this->_subscriberFactory = $this->createPartialMock(
            SubscriberFactory::class,
            ['create']
        );
        $this->_subscriber = $this->createMock(Subscriber::class);
        $this->_subscriber->expects($this->any())->method('loadByEmail')->willReturnSelf();
        $this->_subscriberFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->_subscriber);

        $this->_block = new Info(
            $this->_context,
            $this->currentCustomer,
            $this->_subscriberFactory,
            $this->_helperView
        );
    }

    public function testGetCustomer()
    {
        $this->currentCustomer->expects($this->once())
            ->method('getCustomer')
            ->willReturn($this->_customer);

        $customer = $this->_block->getCustomer();
        $this->assertEquals($customer, $this->_customer);
    }

    public function testGetCustomerException()
    {
        $this->currentCustomer->expects($this->once())
            ->method('getCustomer')
            ->willThrowException(
                new NoSuchEntityException(
                    __(
                        'No such entity with %fieldName = %fieldValue',
                        ['fieldName' => 'customerId', 'fieldValue' => 1]
                    )
                )
            );

        $this->assertNull($this->_block->getCustomer());
    }

    public function testGetName()
    {
        $expectedValue = 'John Q Doe Jr';

        $this->currentCustomer->expects($this->once())
            ->method('getCustomer')
            ->willReturn($this->_customer);

        /**
         * Called three times, once for each attribute (i.e. prefix, middlename, and suffix)
         */
        $this->_helperView->expects($this->any())->method('getCustomerName')->willReturn($expectedValue);

        $this->assertEquals($expectedValue, $this->_block->getName());
    }

    public function testGetChangePasswordUrl()
    {
        $this->assertEquals(self::CHANGE_PASSWORD_URL, $this->_block->getChangePasswordUrl());
    }

    public function testGetSubscriptionObject()
    {
        $this->assertSame($this->_subscriber, $this->_block->getSubscriptionObject());
    }

    /**
     * @param bool $isSubscribed Is the subscriber subscribed?
     * @param bool $expectedValue The expected value - Whether the subscriber is subscribed or not.
     *
     * @dataProvider getIsSubscribedProvider
     */
    public function testGetIsSubscribed($isSubscribed, $expectedValue)
    {
        $this->_subscriber->expects($this->once())->method('isSubscribed')->willReturn($isSubscribed);
        $this->assertEquals($expectedValue, $this->_block->getIsSubscribed());
    }

    /**
     * @return array
     */
    public function getIsSubscribedProvider()
    {
        return [[true, true], [false, false]];
    }

    /**
     * @param bool $isNewsletterEnabled Determines if the newsletter is enabled
     * @param bool $expectedValue The expected value - Whether the newsletter is enabled or not
     *
     * @dataProvider isNewsletterEnabledProvider
     */
    public function testIsNewsletterEnabled($isNewsletterEnabled, $expectedValue)
    {
        $this->_formRegister->expects($this->once())
            ->method('isNewsletterEnabled')
            ->willReturn($isNewsletterEnabled);
        $this->assertEquals($expectedValue, $this->_block->isNewsletterEnabled());
    }

    /**
     * @return array
     */
    public function isNewsletterEnabledProvider()
    {
        return [[true, true], [false, false]];
    }
}
