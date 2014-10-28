<?php
/**
 *
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
namespace Magento\PageCache\Model\Observer;

class FlushCacheByTagsTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\PageCache\Model\Observer\FlushCacheByTags */
    protected $_model;

    /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\PageCache\Model\Config */
    protected $_configMock;

    /** @var  \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\PageCache\Cache */
    protected $_cacheMock;

    /**
     * Set up all mocks and data for test
     */
    public function setUp()
    {
        $this->_configMock = $this->getMock(
            'Magento\PageCache\Model\Config',
            array('getType', 'isEnabled'),
            array(),
            '',
            false
        );
        $this->_cacheMock = $this->getMock('Magento\Framework\App\PageCache\Cache', array('clean'), array(), '', false);

        $this->_model = new \Magento\PageCache\Model\Observer\FlushCacheByTags(
            $this->_configMock,
            $this->_cacheMock
        );
    }

    /**
     * Test case for cache invalidation
     *
     * @dataProvider flushCacheByTagsDataProvider
     * @param $cacheState
     */
    public function testExecute($cacheState)
    {
        $this->_configMock->expects($this->any())->method('isEnabled')->will($this->returnValue($cacheState));
        $observerObject = $this->getMock('Magento\Framework\Event\Observer');
        $observedObject = $this->getMock('Magento\Store\Model\Store', [], [], '', false);

        if ($cacheState) {
            $tags = array('cache_1', 'cache_group');
            $expectedTags = array('cache_1', 'cache_group', 'cache');

            $eventMock = $this->getMock('Magento\Framework\Event', array('getObject'), array(), '', false);
            $eventMock->expects($this->once())->method('getObject')->will($this->returnValue($observedObject));
            $observerObject->expects($this->once())->method('getEvent')->will($this->returnValue($eventMock));
            $this->_configMock->expects(
                $this->once()
            )->method(
                    'getType'
                )->will(
                    $this->returnValue(\Magento\PageCache\Model\Config::BUILT_IN)
                );
            $observedObject->expects($this->once())->method('getIdentities')->will($this->returnValue($tags));

            $this->_cacheMock->expects($this->once())->method('clean')->with($this->equalTo($expectedTags));
        }

        $this->_model->execute($observerObject);
    }

    public function flushCacheByTagsDataProvider()
    {
        return array(
            'full_page cache type is enabled' => array(true),
            'full_page cache type is disabled' => array(false)
        );
    }
}
