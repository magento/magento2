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
 * @category    Magento
 * @package     Magento_CacheInvalidate
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\CacheInvalidate\Model;

class ObserverTest extends \PHPUnit_Framework_TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject | \Magento\CacheInvalidate\Model\Observer */
    protected $_model;

    /** @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Event\Observer */
    protected $_observerMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject | \Magento\HTTP\Adapter\Curl */
    protected $_curlMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject | \Magento\PageCache\Model\Config */
    protected $_configMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject | \Magento\CacheInvalidate\Helper\Data */
    protected $_helperMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Object\ */
    protected $_observerObject;

    /**
     * Set up all mocks and data for test
     */
    public function setUp()
    {
        $this->_configMock = $this->getMock('Magento\PageCache\Model\Config', ['getType'], [], '', false);
        $this->_helperMock = $this->getMock('Magento\PageCache\Helper\Data', ['getUrl'], [], '', false);
        $this->_curlMock = $this->getMock(
            '\Magento\HTTP\Adapter\Curl',
            ['setOptions', 'write', 'read', 'close'],
            [],
            '',
            false
        );
        $this->_model = new \Magento\CacheInvalidate\Model\Observer(
            $this->_configMock,
            $this->_helperMock,
            $this->_curlMock
        );
        $this->_observerMock = $this->getMock('Magento\Event\Observer', ['getEvent'], [], '', false);
        $this->_observerObject = $this->getMock('\Magento\Core\Model\Store', [], [], '', false);
    }

    /**
     * Test case for cache invalidation
     */
    public function testInvalidateVarnish()
    {
        $eventMock = $this->getMock('Magento\Event', ['getObject'], [], '', false);
        $eventMock->expects($this->once())
            ->method('getObject')
            ->will($this->returnValue($this->_observerObject));
        $this->_observerMock->expects($this->once())
            ->method('getEvent')
            ->will($this->returnValue($eventMock));
        $this->_configMock->expects($this->once())
            ->method('getType')
            ->will($this->returnValue(1));
        $tags = array('cache_1', 'cache_group');
        $this->_observerObject->expects($this->once())
            ->method('getIdentities')
            ->will($this->returnValue($tags));
        $this->sendPurgeRequest(implode('|', $tags));

        $this->_model->invalidateVarnish($this->_observerMock);
    }

    /**
     * Test case for flushing all the cache
     */
    public function testFlushAllCache()
    {
        $this->_configMock->expects($this->once())
            ->method('getType')
            ->will($this->returnValue(1));

        $this->sendPurgeRequest('.*');
        $this->_model->flushAllCache($this->_observerMock);
    }

    /**
     * @param array $tags
     */
    protected function sendPurgeRequest($tags = array())
    {
        $url = 'http://mangento.index.php';
        $httpVersion = '1.1';
        $headers = array("X-Magento-Tags-Pattern: {$tags}");
        $this->_helperMock->expects($this->any())
            ->method('getUrl')
            ->with($this->equalTo('*'), array())
            ->will($this->returnValue($url));
        $this->_curlMock->expects($this->once())
            ->method('setOptions')
            ->with(array(CURLOPT_CUSTOMREQUEST => 'PURGE'));
        $this->_curlMock->expects($this->once())
            ->method('write')
            ->with($this->equalTo(''), $this->equalTo($url), $httpVersion, $this->equalTo($headers));
        $this->_curlMock->expects($this->once())
            ->method('read');
        $this->_curlMock->expects($this->once())
            ->method('close');
    }
}
