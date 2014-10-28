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

class InvalidateCacheTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\PageCache\Model\Observer\InvalidateCache */
    protected $_model;

    /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\PageCache\Model\Config */
    protected $_configMock;

    /** @var  \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\Cache\TypeListInterface */
    protected $_typeListMock;

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
        $this->_typeListMock = $this->getMock('Magento\Framework\App\Cache\TypeList', array(), array(), '', false);

        $this->_model = new \Magento\PageCache\Model\Observer\InvalidateCache(
            $this->_configMock,
            $this->_typeListMock
        );
    }

    /**
     * @dataProvider invalidateCacheDataProvider
     * @param bool $cacheState
     */
    public function testExecute($cacheState)
    {
        $this->_configMock->expects($this->once())->method('isEnabled')->will($this->returnValue($cacheState));

        if ($cacheState) {
            $this->_typeListMock->expects($this->once())->method('invalidate')->with($this->equalTo('full_page'));
        }
        $this->_model->execute();
    }

    public function invalidateCacheDataProvider()
    {
        return array(array(true), array(false));
    }

} 
