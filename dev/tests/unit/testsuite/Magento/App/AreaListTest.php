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
namespace Magento\App;

class AreaListTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\App\AreaList
     */
    protected $_model;

    /**
     * @var \Magento\App\Area\FrontNameResolverFactory
     */
    protected $_resolverFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManagerMock;

    protected function setUp()
    {
        $this->objectManagerMock = $this->getMock('Magento\ObjectManager');
        $this->_resolverFactory = $this
            ->getMock('\Magento\App\Area\FrontNameResolverFactory', array(), array(), '', false);
    }

    public function testGetCodeByFrontNameWhenAreaDoesNotContainFrontName()
    {
        $expected = 'expectedFrontName';
        $this->_model = new \Magento\App\AreaList(
            $this->objectManagerMock,
            $this->_resolverFactory,
            array('testArea' => array('frontNameResolver' => 'testValue')),
            $expected
        );

        $resolverMock = $this->getMock('\Magento\App\Area\FrontNameResolverInterface');
        $this->_resolverFactory->expects(
            $this->any()
        )->method(
            'create'
        )->with(
            'testValue'
        )->will(
            $this->returnValue($resolverMock)
        );

        $actual = $this->_model->getCodeByFrontName('testFrontName');
        $this->assertEquals($expected, $actual);
    }

    public function testGetCodeByFrontNameReturnsAreaCode()
    {
        $expected = 'testArea';
        $this->_model = new \Magento\App\AreaList(
            $this->objectManagerMock,
            $this->_resolverFactory,
            array('testArea'=>array('frontName' => 'testFrontName')),
            $expected
        );

        $actual = $this->_model->getCodeByFrontName('testFrontName');
        $this->assertEquals($expected, $actual);
    }

    public function testGetFrontNameWhenAreaCodeAndFrontNameAreSet()
    {
        $expected = 'testFrontName';
        $this->_model = new \Magento\App\AreaList(
            $this->objectManagerMock,
            $this->_resolverFactory,
            array('testAreaCode' => array('frontName' => 'testFrontName')),
            $expected
        );

        $actual = $this->_model->getFrontName('testAreaCode');
        $this->assertEquals($expected, $actual);
    }

    public function testGetFrontNameWhenAreaCodeAndFrontNameArentSet()
    {
        $this->_model = new \Magento\App\AreaList($this->objectManagerMock, $this->_resolverFactory, array(), '');

        $actual = $this->_model->getFrontName('testAreaCode');
        $this->assertNull($actual);
    }

    public function testGetCodes()
    {
        $this->_model = new \Magento\App\AreaList(
            $this->objectManagerMock, $this->_resolverFactory, array('area1' => 'value1', 'area2' => 'value2'), ''
        );

        $expected = array(0 => 'area1', 1 => 'area2');
        $actual = $this->_model->getCodes();
        $this->assertEquals($expected, $actual);
    }
}
