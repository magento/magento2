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
namespace Magento\Cms\Model\Template;

class FilterProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Cms\Model\Template\FilterProvider
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_objectManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_filterMock;

    protected function setUp()
    {
        $this->_filterMock = $this->getMock('Magento\Cms\Model\Template\Filter', array(), array(), '', false);
        $this->_objectManagerMock = $this->getMock('Magento\Framework\ObjectManager');
        $this->_objectManagerMock->expects($this->any())->method('get')->will($this->returnValue($this->_filterMock));
        $this->_model = new \Magento\Cms\Model\Template\FilterProvider($this->_objectManagerMock);
    }

    /**
     * @covers \Magento\Cms\Model\Template\FilterProvider::getBlockFilter
     */
    public function testGetBlockFilter()
    {
        $this->assertInstanceOf('Magento\Cms\Model\Template\Filter', $this->_model->getBlockFilter());
    }

    /**
     * @covers \Magento\Cms\Model\Template\FilterProvider::getPageFilter
     */
    public function testGetPageFilter()
    {
        $this->assertInstanceOf('Magento\Cms\Model\Template\Filter', $this->_model->getPageFilter());
    }

    /**
     * @covers \Magento\Cms\Model\Template\FilterProvider::getPageFilter
     */
    public function testGetPageFilterInnerCache()
    {
        $this->_objectManagerMock->expects($this->once())->method('get')->will($this->returnValue($this->_filterMock));
        $this->_model->getPageFilter();
        $this->_model->getPageFilter();
    }

    /**
     * @covers \Magento\Cms\Model\Template\FilterProvider::getPageFilter
     * @expectedException \Exception
     */
    public function testGetPageWrongInstance()
    {
        $someClassMock = $this->getMock('SomeClass');
        $objectManagerMock = $this->getMock('Magento\Framework\ObjectManager');
        $objectManagerMock->expects($this->once())->method('get')->will($this->returnValue($someClassMock));
        $model = new \Magento\Cms\Model\Template\FilterProvider($objectManagerMock, 'SomeClass', 'SomeClass');
        $model->getPageFilter();
    }
}
