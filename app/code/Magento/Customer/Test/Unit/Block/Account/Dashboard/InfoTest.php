<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Unit\Block\Account\Dashboard;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Customer\Block\Account\Dashboard\Info;

/**
 * Test class for \Magento\Customer\Block\Account\Dashboard\Info.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class InfoTest extends \PHPUnit\Framework\TestCase
{
    /** Constant values used for testing */
    const CUSTOMER_ID = 1;

    const CHANGE_PASSWORD_URL = 'http://localhost/index.php/account/edit/changepass/1';

    const EMAIL_ADDRESS = 'john.doe@example.com';

    /** @var \PHPUnit\Framework\MockObject\MockObject | \Magento\Framework\View\Element\Template\Context */
    private $_context;

    /** @var \PHPUnit\Framework\MockObject\MockObject | \Magento\Customer\Model\Session */
    private $_customerSession;

    /** @var \PHPUnit\Framework\MockObject\MockObject | \Magento\Customer\Api\Data\CustomerInterface */
    private $_customer;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject | \Magento\Customer\Helper\View
     */
    private $_helperView;

    /** @var \PHPUnit\Framework\MockObject\MockObject | \Magento\Newsletter\Model\Subscriber */
    private $_subscriber;

    /** @var \PHPUnit\Framework\MockObject\MockObject | \Magento\Newsletter\Model\SubscriberFactory */
    private $_subscriberFactory;

    /** @var \PHPUnit\Framework\MockObject\MockObject | \Magento\Customer\Block\Form\Register */
    private $_formRegister;

    /** @var Info */
    private $_block;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject | \Magento\Customer\Helper\Session\CurrentCustomer
     */
    protected $currentCustomer;

    protected function setUp(): void
    {
        $this->currentCustomer = $this->createMock(\Magento\Customer\Helper\Session\CurrentCustomer::class);

        $urlBuilder = $this->getMockForAbstractClass(\Magento\Framework\UrlInterface::class, [], '', false);
        $urlBuilder->expects($this->any())->method('getUrl')->willReturn(self::CHANGE_PASSWORD_URL);

        $layout = $this->getMockForAbstractClass(\Magento\Framework\View\LayoutInterface::class, [], '', false);
        $this->_formRegister = $this->createMock(\Magento\Customer\Block\Form\Register::class);
        $layout->expects($this->any())
            ->method('getBlockSingleton')
            ->with(\Magento\Customer\Block\Form\Register::class)
            ->willReturn($this->_formRegister);

        $this->_context = $this->getMockBuilder(\Magento\Framework\View\Element\Template\Context::class)
            ->disableOriginalConstructor()->getMock();
        $this->_context->expects($this->once())->method('getUrlBuilder')->willReturn($urlBuilder);
        $this->_context->expects($this->once())->method('getLayout')->willReturn($layout);

        $this->_customerSession = $this->createMock(\Magento\Customer\Model\Session::class);
        $this->_customerSession->expects($this->any())->method('getId')->willReturn(self::CUSTOMER_ID);

        $this->_customer = $this->createMock(\Magento\Customer\Api\Data\CustomerInterface::class);
        $this->_customer->expects($this->any())->method('getEmail')->willReturn(self::EMAIL_ADDRESS);
        $this->_helperView = $this->getMockBuilder(
            \Magento\Customer\Helper\View::class
        )->disableOriginalConstructor()->getMock();
        $this->_subscriberFactory = $this->createPartialMock(
            \Magento\Newsletter\Model\SubscriberFactory::class,
            ['create']
        );
        $this->_subscriber = $this->createMock(\Magento\Newsletter\Model\Subscriber::class);
        $this->_subscriber->expects($this->any())->method('loadByEmail')->willReturnSelf();
        $this->_subscriberFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->_subscriber);

        $this->_block = new \Magento\Customer\Block\Account\Dashboard\Info(
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
            ->will(
                $this->throwException(new NoSuchEntityException(
                    __(
                        'No such entity with %fieldName = %fieldValue',
                        ['fieldName' => 'customerId', 'fieldValue' => 1]
                    )
                ))
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
