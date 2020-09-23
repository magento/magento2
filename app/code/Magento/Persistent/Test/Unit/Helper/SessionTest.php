<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Persistent\Test\Unit\Helper;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Persistent\Helper\Data as DataHelper;
use Magento\Persistent\Helper\Session as SessionHelper;
use Magento\Persistent\Model\Session;
use Magento\Persistent\Model\SessionFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class \Magento\Persistent\Test\Unit\Helper\SessionTest
 */
class SessionTest extends TestCase
{
    /**
     * @var MockObject|Context
     */
    private $context;

    /**
     * @var  MockObject|SessionHelper
     */
    private $helper;

    /**
     * @var  MockObject|DataHelper
     */
    private $dataHelper;

    /**
     * @var  MockObject|CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var  MockObject|SessionFactory
     */
    private $sessionFactory;

    /**
     * @var  MockObject|Session
     */
    private $session;

    /**
     * @var MockObject|ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * Setup environment
     */
    protected function setUp(): void
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
            ->getMockForAbstractClass();
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

    /**
     * Test isPersistent() function
     *
     * @param int|null $id
     * @param boolean $isEnabled
     * @param boolean $expected
     * @dataProvider isPersistentDataProvider
     */
    public function testIsPersistent($id, $isEnabled, $expected)
    {
        $this->session->expects($this->any())->method('getId')
            ->willReturn($id);
        $this->helper->expects($this->any())->method('getSession')
            ->willReturn($this->session);
        $this->dataHelper->expects($this->any())->method('isEnabled')
            ->willReturn($isEnabled);

        $this->assertEquals($expected, $this->helper->isPersistent());
    }

    /**
     * Data Provider for test isPersistent()
     *
     * @return array
     */
    public function isPersistentDataProvider()
    {
        return [
            'session_id_and_enable_persistent' => [
                1,
                true,
                true
            ],
            'no_session_id_and_enable_persistent' => [
                null,
                true,
                false
            ]
        ];
    }

    /**
     * Test isRememberMeChecked() function
     *
     * @param boolean|null $checked
     * @param boolean $isEnabled
     * @param boolean $isRememberMeEnabled
     * @param boolean $isRememberMeCheckedDefault
     * @param boolean $expected
     * @dataProvider isRememberMeCheckedProvider
     */
    public function testIsRememberMeChecked(
        $checked,
        $isEnabled,
        $isRememberMeEnabled,
        $isRememberMeCheckedDefault,
        $expected
    ) {
        $this->helper->setRememberMeChecked($checked);
        $this->dataHelper->expects($this->any())->method('isEnabled')
            ->willReturn($isEnabled);
        $this->dataHelper->expects($this->any())->method('isRememberMeEnabled')
            ->willReturn($isRememberMeEnabled);
        $this->dataHelper->expects($this->any())->method('isRememberMeCheckedDefault')
            ->willReturn($isRememberMeCheckedDefault);

        $this->assertEquals($expected, $this->helper->isRememberMeChecked());
    }

    /**
     * Data Provider for test isRememberMeChecked()
     *
     * @return array
     */
    public function isRememberMeCheckedProvider()
    {
        return [
            'enable_all_config' => [
                null,
                true,
                true,
                true,
                true
            ],
            'at_least_once_disabled' => [
                null,
                false,
                true,
                true,
                false
            ],
            'set_remember_me_checked_false' => [
                false,
                true,
                true,
                true,
                false
            ],
            'set_remember_me_checked_true' => [
                true,
                false,
                true,
                true,
                true
            ]
        ];
    }
}
