<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Block\Account;

/**
 * Test class for \Magento\Customer\Block\Account\AuthorizationLink
 */
class AuthorizationLinkTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $_objectManager;

    /**
     * \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    /**
     * @var \Magento\Customer\Model\Url
     */
    protected $_customerUrl;

    /**
     * @var \Magento\Customer\Block\Account\AuthorizationLink
     */
    protected $_block;

    protected function setUp()
    {
        $this->_objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->httpContext = $this->getMockBuilder('\Magento\Framework\App\Http\Context')
            ->disableOriginalConstructor()
            ->setMethods(['getValue'])
            ->getMock();
        $this->_customerUrl = $this->getMockBuilder('Magento\Customer\Model\Url')
            ->disableOriginalConstructor()
            ->setMethods(['getLogoutUrl', 'getLoginUrl'])
            ->getMock();

        $context = $this->_objectManager->getObject('Magento\Framework\View\Element\Template\Context');
        $this->_block = $this->_objectManager->getObject(
            'Magento\Customer\Block\Account\AuthorizationLink',
            [
                'context' => $context,
                'httpContext' => $this->httpContext,
                'customerUrl' => $this->_customerUrl,
            ]
        );
    }

    public function testGetLabelLoggedIn()
    {
        $this->httpContext->expects($this->once())
            ->method('getValue')
            ->will($this->returnValue(true));

        $this->assertEquals('Log Out', $this->_block->getLabel());
    }

    public function testGetLabelLoggedOut()
    {
        $this->httpContext->expects($this->once())
            ->method('getValue')
            ->will($this->returnValue(false));

        $this->assertEquals('Log In', $this->_block->getLabel());
    }

    public function testGetHrefLoggedIn()
    {
        $this->httpContext->expects($this->once())
            ->method('getValue')
            ->will($this->returnValue(true));

        $this->_customerUrl->expects($this->once())->method('getLogoutUrl')->will($this->returnValue('logout url'));

        $this->assertEquals('logout url', $this->_block->getHref());
    }

    public function testGetHrefLoggedOut()
    {
        $this->httpContext->expects($this->once())
            ->method('getValue')
            ->will($this->returnValue(false));

        $this->_customerUrl->expects($this->once())->method('getLoginUrl')->will($this->returnValue('login url'));

        $this->assertEquals('login url', $this->_block->getHref());
    }
}
