<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Block\Account;

use Magento\Customer\Block\Account\AuthorizationLink;
use Magento\Customer\Model\Url;
use Magento\Framework\App\Http\Context;
use Magento\Framework\Math\Random;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Helper\SecureHtmlRenderer;
use PHPUnit\Framework\TestCase;

/**
 * Test class for \Magento\Customer\Block\Account\AuthorizationLink
 */
class AuthorizationLinkTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    protected $_objectManager;

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    /**
     * @var Url
     */
    protected $_customerUrl;

    /**
     * @var AuthorizationLink
     */
    protected $_block;

    protected function setUp(): void
    {
        $this->_objectManager = new ObjectManager($this);
        $objects = [
            [
                SecureHtmlRenderer::class,
                $this->createMock(SecureHtmlRenderer::class)
            ],
            [
                Random::class,
                $this->createMock(Random::class)
            ]
        ];
        $this->_objectManager->prepareObjectManager($objects);

        $this->httpContext = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getValue'])
            ->getMock();
        $this->_customerUrl = $this->getMockBuilder(Url::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getLogoutUrl', 'getLoginUrl'])
            ->getMock();

        $context = $this->_objectManager->getObject(\Magento\Framework\View\Element\Template\Context::class);
        $this->_block = $this->_objectManager->getObject(
            AuthorizationLink::class,
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
