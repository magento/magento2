<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Block\Account;

/**
 * Test class for \Magento\Customer\Block\Account\AuthorizationLink
 */
class AuthorizationLinkTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
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

    protected function setUp(): void
    {
        $this->_objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->httpContext = $this->getMockBuilder(\Magento\Framework\App\Http\Context::class)
            ->disableOriginalConstructor()
            ->setMethods(['getValue'])
            ->getMock();
        $this->_customerUrl = $this->getMockBuilder(\Magento\Customer\Model\Url::class)
            ->disableOriginalConstructor()
            ->setMethods(['getLogoutUrl', 'getLoginUrl'])
            ->getMock();

        $context = $this->_objectManager->getObject(\Magento\Framework\View\Element\Template\Context::class);
        $this->_block = $this->_objectManager->getObject(
            \Magento\Customer\Block\Account\AuthorizationLink::class,
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
            ->willReturn(true);

        $this->assertEquals('Sign Out', $this->_block->getLabel());
    }

    public function testGetLabelLoggedOut()
    {
        $this->httpContext->expects($this->once())
            ->method('getValue')
            ->willReturn(false);

        $this->assertEquals('Sign In', $this->_block->getLabel());
    }

    public function testGetHrefLoggedIn()
    {
        $this->httpContext->expects($this->once())
            ->method('getValue')
            ->willReturn(true);

        $this->_customerUrl->expects($this->once())->method('getLogoutUrl')->willReturn('logout url');

        $this->assertEquals('logout url', $this->_block->getHref());
    }

    public function testGetHrefLoggedOut()
    {
        $this->httpContext->expects($this->once())
            ->method('getValue')
            ->willReturn(false);

        $this->_customerUrl->expects($this->once())->method('getLoginUrl')->willReturn('login url');

        $this->assertEquals('login url', $this->_block->getHref());
    }
}
