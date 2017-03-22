<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Integration\Test\Unit\Helper\Oauth;

class DataTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Framework\App\Config\ScopeConfigInterface */
    protected $_scopeConfigMock;

    /** @var \Magento\Integration\Helper\Oauth\Data */
    protected $_dataHelper;

    protected function setUp()
    {
        $this->_scopeConfigMock = $this->getMockBuilder(
            \Magento\Framework\App\Config\ScopeConfigInterface::class
        )->disableOriginalConstructor()->getMock();

        $this->_dataHelper = new \Magento\Integration\Helper\Oauth\Data($this->_scopeConfigMock);
    }

    protected function tearDown()
    {
        unset($this->_scopeConfigMock);
        unset($this->_dataHelper);
    }

    public function testIsCleanupProbabilityZero()
    {
        $this->_scopeConfigMock->expects($this->once())->method('getValue')->will($this->returnValue(0));
        $this->assertFalse($this->_dataHelper->isCleanupProbability());
    }

    public function testIsCleanupProbabilityRandomOne()
    {
        $this->_scopeConfigMock->expects($this->once())->method('getValue')->will($this->returnValue(1));
        $this->assertTrue($this->_dataHelper->isCleanupProbability());
    }

    public function testGetCleanupExpirationPeriodZero()
    {
        $this->_scopeConfigMock->expects($this->once())->method('getValue')->will($this->returnValue(0));
        $this->assertEquals(
            \Magento\Integration\Helper\Oauth\Data::CLEANUP_EXPIRATION_PERIOD_DEFAULT,
            $this->_dataHelper->getCleanupExpirationPeriod()
        );
    }

    public function testGetCleanupExpirationPeriodNonZero()
    {
        $this->_scopeConfigMock->expects($this->once())->method('getValue')->will($this->returnValue(10));
        $this->assertEquals(10, $this->_dataHelper->getCleanupExpirationPeriod());
    }

    public function testConsumerPostMaxRedirectsZero()
    {
        $this->_scopeConfigMock->expects($this->once())->method('getValue')->will($this->returnValue(0));
        $this->assertEquals(0, $this->_dataHelper->getConsumerPostMaxRedirects());
    }

    public function testConsumerPostMaxRedirectsNonZero()
    {
        $this->_scopeConfigMock->expects($this->once())->method('getValue')->will($this->returnValue(10));
        $this->assertEquals(10, $this->_dataHelper->getConsumerPostMaxRedirects());
    }

    public function testGetConsumerPostTimeoutZero()
    {
        $this->_scopeConfigMock->expects($this->once())->method('getValue')->will($this->returnValue(0));
        $this->assertEquals(
            \Magento\Integration\Helper\Oauth\Data::CONSUMER_POST_TIMEOUT_DEFAULT,
            $this->_dataHelper->getConsumerPostTimeout()
        );
    }

    public function testGetConsumerPostTimeoutNonZero()
    {
        $this->_scopeConfigMock->expects($this->once())->method('getValue')->will($this->returnValue(10));
        $this->assertEquals(10, $this->_dataHelper->getConsumerPostTimeout());
    }
}
