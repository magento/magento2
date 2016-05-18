<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\CustomerData\Plugin;

use Magento\Customer\CustomerData\Plugin\SessionChecker;

class SessionCheckerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SessionChecker
     */
    protected $plugin;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\PhpCookieManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $cookieManager;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $metadataFactory;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\CookieMetadata|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $metadata;

    /**
     * @var \Magento\Customer\Model\Session|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $session;

    /**
     * @var \Magento\Framework\App\Response\Http|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $response;

    public function setUp()
    {
        $this->cookieManager = $this->getMockBuilder('Magento\Framework\Stdlib\Cookie\PhpCookieManager')
            ->disableOriginalConstructor()
            ->getMock();
        $this->metadataFactory = $this->getMockBuilder('Magento\Framework\Stdlib\Cookie\CookieMetadataFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $this->metadata = $this->getMockBuilder('Magento\Framework\Stdlib\Cookie\CookieMetadata')
            ->disableOriginalConstructor()
            ->getMock();
        $this->session = $this->getMockBuilder('Magento\Customer\Model\Session')
            ->disableOriginalConstructor()
            ->getMock();
        $this->response = $this->getMockBuilder('Magento\Framework\App\Response\Http')
            ->disableOriginalConstructor()
            ->getMock();

        $this->plugin = new SessionChecker($this->cookieManager, $this->metadataFactory, $this->session);
    }

    /**
     * @param bool $result
     * @param string $callCount
     * @return void
     * @dataProvider testAfterIsLoggedInDataProvider
     */
    public function testBeforeSendVary($result, $callCount)
    {
        $this->session->expects($this->once())
            ->method('isLoggedIn')
            ->willReturn($result);

        $this->metadataFactory->expects($this->{$callCount}())
            ->method('createCookieMetadata')
            ->willReturn($this->metadata);
        $this->metadata->expects($this->{$callCount}())
            ->method('setPath')
            ->with('/');
        $this->cookieManager->expects($this->{$callCount}())
            ->method('deleteCookie')
            ->with('mage-cache-sessid', $this->metadata);
        $this->plugin->beforeSendVary($this->response);
    }

    public function testAfterIsLoggedInDataProvider()
    {
        return [
            [false, 'once'],
            [true, 'never']
        ];
    }
}
