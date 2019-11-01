<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Persistent\Test\Unit\Helper;

use Magento\Persistent\Helper\Session as SessionHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Persistent\Helper\Data as DataHelper;
use Magento\Persistent\Model\SessionFactory;
use Magento\Persistent\Model\Session;

/**
 * Class \Magento\Persistent\Test\Unit\Helper\SessionTest
 */
class SessionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|Context
     */
    private $context;

    /**
     * @var  \PHPUnit_Framework_MockObject_MockObject|SessionHelper
     */
    private $helper;

    /**
     * @var  \PHPUnit_Framework_MockObject_MockObject|DataHelper
     */
    private $dataHelper;

    /**
     * @var  \PHPUnit_Framework_MockObject_MockObject|CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var  \PHPUnit_Framework_MockObject_MockObject|SessionFactory
     */
    private $sessionFactory;

    /**
     * @var  \PHPUnit_Framework_MockObject_MockObject|Session
     */
    private $session;

    /**
     * Setup environment
     */
    protected function setUp()
    {
        $this->context = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->dataHelper = $this->getMockBuilder(DataHelper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->checkoutSession = $this->getMockBuilder(CheckoutSession::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->scopeConfig = $this->getMockBuilder(ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->sessionFactory = $this->getMockBuilder(SessionFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->session = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->sessionFactory->expects($this->any())->method('create')->willReturn($this->session);

        $this->helper = $this->getMockBuilder(SessionHelper::class)
            ->setMethods(['getSession'])
            ->setConstructorArgs(
                [
                    'context' => $this->context,
                    'persistentData' => $this->dataHelper,
                    'checkoutSession' => $this->checkoutSession,
                    'sessionFactory' => $this->sessionFactory
                ]
            )
            ->getMock();
    }

    /***
     * Test isPersistent() when the session has id and enable persistent
     */
    public function testIsPersistentWhenSessionId()
    {
        $this->session->expects($this->any())->method('getId')
            ->willReturn(1);
        $this->helper->expects($this->any())->method('getSession')
            ->willReturn($this->session);
        $this->dataHelper->expects($this->any())->method('isEnabled')
            ->willReturn(true);

        $this->assertEquals(true, $this->helper->isPersistent());
    }

    /***
     * Test isPersistent() when the no session id and enable persistent
     */
    public function testIsPersistentWhenNoSessionId()
    {
        $this->session->expects($this->any())->method('getId')
            ->willReturn(null);
        $this->helper->expects($this->any())->method('getSession')
            ->willReturn($this->session);
        $this->dataHelper->expects($this->any())->method('isEnabled')
            ->willReturn(true);

        $this->assertEquals(false, $this->helper->isPersistent());
    }

    /**
     * Test isRememberMeChecked() when enable all config
     */
    public function testIsRememberMeCheckedWhenEnabledAll()
    {
        $testCase = [
            'dataset' => [
                'enabled' => true,
                'remember_me_enabled' => true,
                'remember_me_checked_default' => true
            ],
            'expected' => true
        ];
        $this->executeTestIsRememberMeChecked($testCase);
    }

    /**
     * Test isRememberMeChecked() when config persistent is disabled
     */
    public function testIsRememberMeCheckedWhenAtLeastOnceDisabled()
    {
        $testCase = [
            'dataset' => [
                'enabled' => false,
                'remember_me_enabled' => true,
                'remember_me_checked_default' => true
            ],
            'expected' => false
        ];
        $this->executeTestIsRememberMeChecked($testCase);
    }

    /**
     * Test isRememberMeChecked() when setRememberMeChecked(false)
     */
    public function testIsRememberMeCheckedWhenSetValue()
    {
        $testCase = [
            'dataset' => [
                'enabled' => true,
                'remember_me_enabled' => true,
                'remember_me_checked_default' => true
            ],
            'expected' => false
        ];
        $this->helper->setRememberMeChecked(false);
        $this->executeTestIsRememberMeChecked($testCase);
    }

    /**
     * Execute test isRememberMeChecked() function
     *
     * @param array $testCase
     */
    public function executeTestIsRememberMeChecked($testCase)
    {
        $this->dataHelper->expects($this->any())->method('isEnabled')
            ->willReturn($testCase['dataset']['enabled']);
        $this->dataHelper->expects($this->any())->method('isRememberMeEnabled')
            ->willReturn($testCase['dataset']['remember_me_enabled']);
        $this->dataHelper->expects($this->any())->method('isRememberMeCheckedDefault')
            ->willReturn($testCase['dataset']['remember_me_checked_default']);
        $this->assertEquals($testCase['expected'], $this->helper->isRememberMeChecked());
    }
}
