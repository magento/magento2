<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Block\Account;

use Magento\Customer\Block\Account\RegisterLink;
use Magento\Customer\Model\Context;
use Magento\Customer\Model\Registration;
use Magento\Customer\Model\Url;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * Test class for \Magento\Customer\Block\Account\RegisterLink
 */
class RegisterLinkTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    protected $_objectManager;

    protected function setUp(): void
    {
        $this->_objectManager = new ObjectManager($this);
    }

    /**
     * @param bool $isAuthenticated
     * @param bool $isRegistrationAllowed
     * @param bool $result
     * @dataProvider dataProviderToHtml
     * @return void
     */
    public function testToHtml($isAuthenticated, $isRegistrationAllowed, $result)
    {
        $context = $this->_objectManager->getObject(\Magento\Framework\View\Element\Template\Context::class);

        $httpContext = $this->getMockBuilder(\Magento\Framework\App\Http\Context::class)
            ->disableOriginalConstructor()
            ->setMethods(['getValue'])
            ->getMock();
        $httpContext->expects($this->any())
            ->method('getValue')
            ->with(Context::CONTEXT_AUTH)
            ->willReturn($isAuthenticated);

        $registrationMock = $this->getMockBuilder(Registration::class)
            ->disableOriginalConstructor()
            ->setMethods(['isAllowed'])
            ->getMock();
        $registrationMock->expects($this->any())
            ->method('isAllowed')
            ->willReturn($isRegistrationAllowed);

        /** @var RegisterLink $link */
        $link = $this->_objectManager->getObject(
            RegisterLink::class,
            [
                'context' => $context,
                'httpContext' => $httpContext,
                'registration' => $registrationMock,
            ]
        );

        $this->assertEquals($result, $link->toHtml() === '');
    }

    /**
     * @return array
     */
    public function dataProviderToHtml()
    {
        return [
            [true, true, true],
            [false, false, true],
            [true, false, true],
            [false, true, false],
        ];
    }

    public function testGetHref()
    {
        $this->_objectManager = new ObjectManager($this);
        $helper = $this->getMockBuilder(
            Url::class
        )->disableOriginalConstructor()
            ->setMethods(
                ['getRegisterUrl']
            )->getMock();

        $helper->expects($this->any())->method('getRegisterUrl')->willReturn('register url');

        $context = $this->_objectManager->getObject(\Magento\Framework\View\Element\Template\Context::class);

        $block = $this->_objectManager->getObject(
            RegisterLink::class,
            ['context' => $context, 'customerUrl' => $helper]
        );
        $this->assertEquals('register url', $block->getHref());
    }
}
