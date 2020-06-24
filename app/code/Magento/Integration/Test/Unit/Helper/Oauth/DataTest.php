<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Integration\Test\Unit\Helper\Oauth;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Integration\Helper\Oauth\Data;
use PHPUnit\Framework\TestCase;

class DataTest extends TestCase
{
    /** @var ScopeConfigInterface */
    protected $_scopeConfigMock;

    /** @var Data */
    protected $_dataHelper;

    protected function setUp(): void
    {
        $this->_scopeConfigMock = $this->getMockBuilder(
            ScopeConfigInterface::class
        )->disableOriginalConstructor()
            ->getMock();

        $this->_dataHelper = new Data($this->_scopeConfigMock);
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
            Data::CLEANUP_EXPIRATION_PERIOD_DEFAULT,
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
            Data::CONSUMER_POST_TIMEOUT_DEFAULT,
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
