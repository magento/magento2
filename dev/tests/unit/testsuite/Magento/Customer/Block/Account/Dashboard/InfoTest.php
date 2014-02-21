<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Customer\Block\Account\Dashboard;

use Magento\Exception\NoSuchEntityException;

/**
 * Test class for \Magento\Customer\Block\Account\Dashboard\Info.
 */
class InfoTest extends \PHPUnit_Framework_TestCase
{
    /** Constant values used for testing */
    const CUSTOMER_ID = 1;
    const CHANGE_PASSWORD_URL = 'http://localhost/index.php/account/edit/changepass/1';
    const EMAIL_ADDRESS = 'john.doe@ebay.com';

    /** @var \PHPUnit_Framework_MockObject_MockObject | \Magento\View\Element\Template\Context */
    private $_context;

    /** @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Customer\Model\Session */
    private $_customerSession;

    /** @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Customer\Service\V1\CustomerServiceInterface */
    private $_customerService;

    /** @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Customer\Service\V1\Dto\Customer */
    private $_customer;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Customer\Service\V1\CustomerMetadataServiceInterface
     */
    private $_metadataService;

    /** @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Newsletter\Model\Subscriber */
    private $_subscriber;

    /** @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Newsletter\Model\SubscriberFactory */
    private $_subscriberFactory;

    /** @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Customer\Block\Form\Register */
    private $_formRegister;

    /** @var Info */
    private $_block;

    public function setUp()
    {
        $urlBuilder = $this->getMockForAbstractClass('Magento\UrlInterface', array(), '', false);
        $urlBuilder->expects($this->any())->method('getUrl')->will($this->returnValue(self::CHANGE_PASSWORD_URL));

        $layout = $this->getMockForAbstractClass('Magento\View\LayoutInterface', array(), '', false);
        $this->_formRegister = $this->getMock('Magento\Customer\Block\Form\Register', array(), array(), '', false);
        $layout->expects($this->any())
            ->method('getBlockSingleton')
            ->with('Magento\Customer\Block\Form\Register')->will($this->returnValue($this->_formRegister));

        $this->_context = $this->getMock('Magento\View\Element\Template\Context', array(), array(), '', false);
        $this->_context->expects($this->once())->method('getUrlBuilder')->will($this->returnValue($urlBuilder));
        $this->_context->expects($this->once())->method('getLayout')->will($this->returnValue($layout));

        $this->_customerSession = $this->getMock('Magento\Customer\Model\Session', array(), array(), '', false);
        $this->_customerSession->expects($this->any())->method('getId')->will($this->returnValue(self::CUSTOMER_ID));

        $this->_customerService = $this->getMockForAbstractClass(
            'Magento\Customer\Service\V1\CustomerServiceInterface', array(), '', false
        );
        $this->_customer = $this->getMock('Magento\Customer\Service\V1\Dto\Customer', array(), array(), '', false);
        $this->_customer->expects($this->any())->method('getEmail')->will($this->returnValue(self::EMAIL_ADDRESS));
        $this->_customerService
            ->expects($this->any())->method('getCustomer')->will($this->returnValue($this->_customer));

        $this->_metadataService = $this->getMockForAbstractClass(
            'Magento\Customer\Service\V1\CustomerMetadataServiceInterface', array(), '', false
        );
        $this->_subscriberFactory =
            $this->getMock('Magento\Newsletter\Model\SubscriberFactory', array('create'), array(), '', false);
        $this->_subscriber = $this->getMock('Magento\Newsletter\Model\Subscriber', array(), array(), '', false);
        $this->_subscriber->expects($this->any())->method('loadByEmail')->will($this->returnSelf());
        $this->_subscriberFactory
            ->expects($this->any())->method('create')->will($this->returnValue($this->_subscriber));

        $this->_block = new Info(
            $this->_context,
            $this->_customerSession,
            $this->_customerService,
            $this->_metadataService,
            $this->_subscriberFactory
        );
    }

    public function testGetCustomer()
    {
        $this->_customer->expects($this->once())->method('getCustomerId')->will($this->returnValue(self::CUSTOMER_ID));
        $customer = $this->_block->getCustomer();
        $this->assertEquals(self::CUSTOMER_ID, $customer->getCustomerId());
    }

    public function testGetCustomerException()
    {
        $this->_customerService
            ->expects($this->once())
            ->method('getCustomer')->will($this->throwException(new NoSuchEntityException('customerId', 1)));
        $this->assertNull($this->_block->getCustomer());
    }

    /**
     * Tests variations of Account\Dashboard\Info::getName() by controlling the visibility of the prefix,
     * middlename, and suffix Customer attributes. Also tests variations based on whether the Customer has
     * values for these attributes. All Customers have a first and last name.
     *
     * @param array $isVisible Determines the visibility of the prefix, middlename, and suffix attriubtes
     * @param string $prefix Customer prefix attribute value
     * @param string $firstname Customer firstname attribute value
     * @param string $middlename Customer middlename attribute value
     * @param string $lastname Customer lastname attribute value
     * @param string $suffix Customer suffix attribute value
     * @param string $expectedValue Concatenation of all visible Customer attribute values
     *
     * @dataProvider getNameProvider
     */
    public function testGetName(
        array $isVisible, $prefix, $firstname, $middlename, $lastname, $suffix, $expectedValue
    ) {
        $attributeMetadata =
            $this->getMock('Magento\Customer\Service\V1\Dto\Eav\AttributeMetadata', array(), array(), '', false);

        /**
         * Called three times, once for each attribute (i.e. prefix, middlename, and suffix)
         */
        $this->_metadataService
            ->expects($this->any())
            ->method('getCustomerAttributeMetadata')->will($this->returnValue($attributeMetadata));

        /**
         * Sets the value of AttributeMetadata::isVisible() for the prefix at(0), middlename at(1) and
         * suffix at(2) Customer attributes.
         */
        foreach ($isVisible as $index => $boolean) {
            $attributeMetadata
                ->expects($this->at($index))->method('isVisible')->will($this->returnValue($boolean));
        }

        /**
         * The AttributeMetadata::{getPrefix() | getMiddlename() | getSuffix()} methods are called twice,
         * while getFirstname() and getLastname() are only called once. Hence the use of any() vs. once().
         */
        $this->_customer->expects($this->any())->method('getPrefix')->will($this->returnValue($prefix));
        $this->_customer->expects($this->once())->method('getFirstname')->will($this->returnValue($firstname));
        $this->_customer->expects($this->any())->method('getMiddlename')->will($this->returnValue($middlename));
        $this->_customer->expects($this->once())->method('getLastname')->will($this->returnValue($lastname));
        $this->_customer->expects($this->any())->method('getSuffix')->will($this->returnValue($suffix));

        $this->assertEquals($expectedValue, $this->_block->getName());
    }

    /**
     * @return array
     */
    public function getNameProvider()
    {
        return array(
            array([false, true,  true],  'Mr', 'John', 'Q',  'Doe', 'Jr', 'John Q Doe Jr'),
            array([true,  false, true],  'Mr', 'John', 'Q',  'Doe', 'Jr', 'Mr John Doe Jr'),
            array([true,  true,  false], 'Mr', 'John', 'Q',  'Doe', 'Jr', 'Mr John Q Doe'),
            array([false, false, false], 'Mr', 'John', 'Q',  'Doe', 'Jr', 'John Doe'),
            array([true,  true,  true],  null, 'John', 'Q',  'Doe', 'Jr', 'John Q Doe Jr'),
            array([true,  true,  true],  'Mr', 'John', null, 'Doe', 'Jr', 'Mr John Doe Jr'),
            array([true,  true,  true],  'Mr', 'John', 'Q',  'Doe', null, 'Mr John Q Doe'),
            array([true,  true,  true],  null, 'John', null, 'Doe', null, 'John Doe')
        );
    }

    public function testGetNameWithNoSuchEntityException()
    {
        /**
         * Called three times, once for each attribute (i.e. prefix, middlename, and suffix)
         */
        $this->_metadataService
            ->expects($this->any())
            ->method('getCustomerAttributeMetadata')
            ->will($this->throwException(new NoSuchEntityException('field', 'value')));

        /**
         * The AttributeMetadata::{getPrefix() | getMiddlename() | getSuffix()} methods are called twice,
         * while getFirstname() and getLastname() are only called once. Hence the use of any() vs. once().
         */
        $this->_customer->expects($this->any())->method('getPrefix')->will($this->returnValue('prefix'));
        $this->_customer->expects($this->once())->method('getFirstname')->will($this->returnValue('firstname'));
        $this->_customer->expects($this->any())->method('getMiddlename')->will($this->returnValue('middlename'));
        $this->_customer->expects($this->once())->method('getLastname')->will($this->returnValue('lastname'));
        $this->_customer->expects($this->any())->method('getSuffix')->will($this->returnValue('suffix'));

        $this->assertEquals('firstname lastname', $this->_block->getName());
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
        $this->_subscriber->expects($this->once())->method('isSubscribed')->will($this->returnValue($isSubscribed));
        $this->assertEquals($expectedValue, $this->_block->getIsSubscribed());
    }

    /**
     * @return array
     */
    public function getIsSubscribedProvider()
    {
        return array(
            array(true, true),
            array(false, false)
        );
    }

    /**
     * @param bool $isNewsletterEnabled Determines if the newsletter is enabled
     * @param bool $expectedValue The expected value - Whether the newsletter is enabled or not
     *
     * @dataProvider isNewsletterEnabledProvider
     */
    public function testIsNewsletterEnabled($isNewsletterEnabled, $expectedValue)
    {
        $this->_formRegister
            ->expects($this->once())
            ->method('isNewsletterEnabled')->will($this->returnValue($isNewsletterEnabled));
        $this->assertEquals($expectedValue, $this->_block->isNewsletterEnabled());
    }

    public function isNewsletterEnabledProvider()
    {
        return array(
            array(true, true),
            array(false, false)
        );
    }
}
