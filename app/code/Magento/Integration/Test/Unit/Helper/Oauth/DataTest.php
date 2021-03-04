<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Integration\Test\Unit\Helper\Oauth;

class DataTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Framework\App\Config\ScopeConfigInterface */
    protected $_scopeConfigMock;

    /** @var \Magento\Integration\Helper\Oauth\Data */
    protected $_dataHelper;

    protected function setUp(): void
    {
        $this->_scopeConfigMock = $this->getMockBuilder(
            \Magento\Framework\App\Config\ScopeConfigInterface::class
        )->disableOriginalConstructor()->getMock();

        $this->_dataHelper = new \Magento\Integration\Helper\Oauth\Data($this->_scopeConfigMock);
    }

    protected function tearDown(): void
    {
        unset($this->_scopeConfigMock);
        unset($this->_dataHelper);
    }

    public function testIsCleanupProbabilityZero()
    {
        $this->_scopeConfigMock->expects($this->once())->method('getValue')->willReturn(0);
        $this->assertFalse($this->_dataHelper->isCleanupProbability());
    }

    public function testIsCleanupProbabilityRandomOne()
    {
        $this->_scopeConfigMock->expects($this->once())->method('getValue')->willReturn(1);
        $this->assertTrue($this->_dataHelper->isCleanupProbability());
    }

    public function testGetCleanupExpirationPeriodZero()
    {
        $this->_scopeConfigMock->expects($this->once())->method('getValue')->willReturn(0);
        $this->assertEquals(
            \Magento\Integration\Helper\Oauth\Data::CLEANUP_EXPIRATION_PERIOD_DEFAULT,
            $this->_dataHelper->getCleanupExpirationPeriod()
        );
    }

    public function testGetCleanupExpirationPeriodNonZero()
    {
        $this->_scopeConfigMock->expects($this->once())->method('getValue')->willReturn(10);
        $this->assertEquals(10, $this->_dataHelper->getCleanupExpirationPeriod());
    }

    public function testConsumerPostMaxRedirectsZero()
    {
        $this->_scopeConfigMock->expects($this->once())->method('getValue')->willReturn(0);
        $this->assertEquals(0, $this->_dataHelper->getConsumerPostMaxRedirects());
    }

    public function testConsumerPostMaxRedirectsNonZero()
    {
        $this->_scopeConfigMock->expects($this->once())->method('getValue')->willReturn(10);
        $this->assertEquals(10, $this->_dataHelper->getConsumerPostMaxRedirects());
    }

    public function testGetConsumerPostTimeoutZero()
    {
        $this->_scopeConfigMock->expects($this->once())->method('getValue')->willReturn(0);
        $this->assertEquals(
            \Magento\Integration\Helper\Oauth\Data::CONSUMER_POST_TIMEOUT_DEFAULT,
            $this->_dataHelper->getConsumerPostTimeout()
        );
    }

    public function testGetConsumerPostTimeoutNonZero()
    {
        $this->_scopeConfigMock->expects($this->once())->method('getValue')->willReturn(10);
        $this->assertEquals(10, $this->_dataHelper->getConsumerPostTimeout());
    }

    public function testGetCustomerTokenLifetimeNotEmpty()
    {
        $this->_scopeConfigMock
            ->expects($this->once())
            ->method('getValue')
            ->with('oauth/access_token_lifetime/customer')
            ->willReturn(10);
        $this->assertEquals(10, $this->_dataHelper->getCustomerTokenLifetime());
    }

    public function testGetCustomerTokenLifetimeEmpty()
    {
        $this->_scopeConfigMock
            ->expects($this->once())
            ->method('getValue')
            ->with('oauth/access_token_lifetime/customer')
            ->willReturn(null);
        $this->assertEquals(0, $this->_dataHelper->getCustomerTokenLifetime());
    }

    public function testGetAdminTokenLifetimeNotEmpty()
    {
        $this->_scopeConfigMock
            ->expects($this->once())
            ->method('getValue')
            ->with('oauth/access_token_lifetime/admin')
            ->willReturn(10);
        $this->assertEquals(10, $this->_dataHelper->getAdminTokenLifetime());
    }

    public function testGetAdminTokenLifetimeEmpty()
    {
        $this->_scopeConfigMock
            ->expects($this->once())
            ->method('getValue')
            ->with('oauth/access_token_lifetime/admin')
            ->willReturn(null);
        $this->assertEquals(0, $this->_dataHelper->getAdminTokenLifetime());
    }
}
