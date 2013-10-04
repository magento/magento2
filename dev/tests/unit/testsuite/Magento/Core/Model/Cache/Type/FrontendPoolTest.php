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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Core\Model\Cache\Type;

class FrontendPoolTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Core\Model\Cache\Type\FrontendPool
     */
    protected $_model;

    /**
     * @var \Magento\ObjectManager|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_objectManager;

    /**
     * @var \Magento\Core\Model\Cache\Frontend\Pool|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_cachePool;

    protected function setUp()
    {
        $this->_objectManager = $this->getMock('Magento\ObjectManager', array(), array(), '', false);
        $this->_cachePool = $this->getMock('Magento\Core\Model\Cache\Frontend\Pool', array(), array(), '', false);
        $this->_model = new \Magento\Core\Model\Cache\Type\FrontendPool($this->_objectManager, $this->_cachePool);
    }

    public function testGet()
    {
        $instanceMock = $this->getMock('Magento\Cache\FrontendInterface');
        $this->_cachePool->expects($this->once())
            ->method('get')
            ->with('cache_type')
            ->will($this->returnValue($instanceMock));

        $accessMock = $this->getMock('Magento\Core\Model\Cache\Type\AccessProxy', array(), array(), '', false);
        $this->_objectManager->expects($this->once())
            ->method('create')
            ->with('Magento\Core\Model\Cache\Type\AccessProxy',
                array('frontend' => $instanceMock, 'identifier' => 'cache_type'))
            ->will($this->returnValue($accessMock));

        $instance = $this->_model->get('cache_type');
        $this->assertSame($accessMock, $instance);

        // And must be cached
        $instance = $this->_model->get('cache_type');
        $this->assertSame($accessMock, $instance);
    }

    public function testGetFallbackToDefaultId()
    {
        /**
         * Setup cache pool to have knowledge only about default cache instance. Also check appropriate sequence
         * of calls.
         */
        $defaultInstance = $this->getMock('Magento\Cache\FrontendInterface');
        $this->_cachePool->expects($this->at(0))
            ->method('get')
            ->with('cache_type')
            ->will($this->returnValue(null));
        $this->_cachePool->expects($this->at(1))
            ->method('get')
            ->with(\Magento\Core\Model\Cache\Frontend\Pool::DEFAULT_FRONTEND_ID)
            ->will($this->returnValue($defaultInstance));

        $this->_cachePool->expects($this->at(2))
            ->method('get')
            ->with('another_cache_type')
            ->will($this->returnValue(null));
        $this->_cachePool->expects($this->at(3))
            ->method('get')
            ->with(\Magento\Core\Model\Cache\Frontend\Pool::DEFAULT_FRONTEND_ID)
            ->will($this->returnValue($defaultInstance));

        /**
         * Setup object manager to create new access proxies. We expect two calls.
         */
        $this->_objectManager->expects($this->at(0))
            ->method('create')
            ->with('Magento\Core\Model\Cache\Type\AccessProxy',
                array('frontend' => $defaultInstance, 'identifier' => 'cache_type'))
            ->will($this->returnValue(
                $this->getMock('Magento\Core\Model\Cache\Type\AccessProxy', array(), array(), '', false)
        ));
        $this->_objectManager->expects($this->at(1))
            ->method('create')
            ->with('Magento\Core\Model\Cache\Type\AccessProxy',
                array('frontend' => $defaultInstance, 'identifier' => 'another_cache_type'))
            ->will($this->returnValue(
                $this->getMock('Magento\Core\Model\Cache\Type\AccessProxy', array(), array(), '', false)
        ));

        $cacheInstance = $this->_model->get('cache_type');
        $anotherInstance = $this->_model->get('another_cache_type');
        $this->assertNotSame($cacheInstance, $anotherInstance,
            'Different cache instances must be returned for different identifiers');
    }
}
