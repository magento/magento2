<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Block\Account;

use Magento\Customer\Model\Context;

/**
 * Test class for \Magento\Customer\Block\Account\RegisterLink
 */
class RegisterLinkTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $_objectManager;

    protected function setUp()
    {
        $this->_objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
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
        $context = $this->_objectManager->getObject('Magento\Framework\View\Element\Template\Context');

        $httpContext = $this->getMockBuilder('Magento\Framework\App\Http\Context')
            ->disableOriginalConstructor()
            ->setMethods(['getValue'])
            ->getMock();
        $httpContext->expects($this->any())
            ->method('getValue')
            ->with(Context::CONTEXT_AUTH)
            ->will($this->returnValue($isAuthenticated));

        $registrationMock = $this->getMockBuilder('Magento\Customer\Model\Registration')
            ->disableOriginalConstructor()
            ->setMethods(['isAllowed'])
            ->getMock();
        $registrationMock->expects($this->any())
            ->method('isAllowed')
            ->will($this->returnValue($isRegistrationAllowed));

        /** @var \Magento\Customer\Block\Account\RegisterLink $link */
        $link = $this->_objectManager->getObject(
            'Magento\Customer\Block\Account\RegisterLink',
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
        $this->_objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $helper = $this->getMockBuilder(
            'Magento\Customer\Model\Url'
        )->disableOriginalConstructor()->setMethods(
            ['getRegisterUrl']
        )->getMock();

        $helper->expects($this->any())->method('getRegisterUrl')->will($this->returnValue('register url'));

        $context = $this->_objectManager->getObject('Magento\Framework\View\Element\Template\Context');

        $block = $this->_objectManager->getObject(
            'Magento\Customer\Block\Account\RegisterLink',
            ['context' => $context, 'customerUrl' => $helper]
        );
        $this->assertEquals('register url', $block->getHref());
    }
}
