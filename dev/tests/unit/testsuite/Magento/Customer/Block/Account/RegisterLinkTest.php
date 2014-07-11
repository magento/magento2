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
namespace Magento\Customer\Block\Account;

/**
 * Test class for \Magento\Customer\Block\Account\RegisterLink
 */
class RegisterLinkTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $_objectManager;

    protected function setUp()
    {
        $this->_objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
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
            ->setMethods(array('getValue'))
            ->getMock();
        $httpContext->expects($this->any())
            ->method('getValue')
            ->with(\Magento\Customer\Helper\Data::CONTEXT_AUTH)
            ->will($this->returnValue($isAuthenticated));

        $helperMock = $this->getMockBuilder('Magento\Customer\Helper\Data')
            ->disableOriginalConstructor()
            ->setMethods(array('isRegistrationAllowed', 'getRegisterUrl'))
            ->getMock();
        $helperMock->expects($this->any())
            ->method('isRegistrationAllowed')
            ->will($this->returnValue($isRegistrationAllowed));

        /** @var \Magento\Customer\Block\Account\RegisterLink $link */
        $link = $this->_objectManager->getObject(
            'Magento\Customer\Block\Account\RegisterLink',
            array(
                'context' => $context,
                'httpContext' => $httpContext,
                'customerHelper' => $helperMock,
            )
        );

        $this->assertEquals($result, $link->toHtml() === '');
    }

    /**
     * @return array
     */
    public function dataProviderToHtml()
    {
        return array(
            array(true, true, true),
            array(false, false, true),
            array(true, false, true),
            array(false, true, false),
        );
    }

    public function testGetHref()
    {
        $this->_objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $helper = $this->getMockBuilder(
            'Magento\Customer\Helper\Data'
        )->disableOriginalConstructor()->setMethods(
            array('getRegisterUrl')
        )->getMock();

        $helper->expects($this->any())->method('getRegisterUrl')->will($this->returnValue('register url'));

        $context = $this->_objectManager->getObject('Magento\Framework\View\Element\Template\Context');

        $block = $this->_objectManager->getObject(
            'Magento\Customer\Block\Account\RegisterLink',
            array('context' => $context, 'customerHelper' => $helper)
        );
        $this->assertEquals('register url', $block->getHref());
    }
}
