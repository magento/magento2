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
namespace Magento\Integration\Helper\Oauth;

class DataTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Framework\App\Config\ScopeConfigInterface */
    protected $_scopeConfigMock;

    /** @var \Magento\Integration\Helper\Oauth\Data */
    protected $_dataHelper;

    protected function setUp()
    {
        $this->_scopeConfigMock = $this->getMockBuilder(
            'Magento\Framework\App\Config\ScopeConfigInterface'
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
