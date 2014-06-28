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
namespace Magento\Framework\App;

class AreaListTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\App\AreaList
     */
    protected $_model;

    /**
     * @var \Magento\Framework\App\Area\FrontNameResolverFactory
     */
    protected $_resolverFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManagerMock;

    protected function setUp()
    {
        $this->objectManagerMock = $this->getMock('Magento\Framework\ObjectManager');
        $this->_resolverFactory = $this
            ->getMock('\Magento\Framework\App\Area\FrontNameResolverFactory', array(), array(), '', false);
    }

    public function testGetCodeByFrontNameWhenAreaDoesNotContainFrontName()
    {
        $expected = 'expectedFrontName';
        $this->_model = new \Magento\Framework\App\AreaList(
            $this->objectManagerMock,
            $this->_resolverFactory,
            array('testArea' => array('frontNameResolver' => 'testValue')),
            $expected
        );

        $resolverMock = $this->getMock('\Magento\Framework\App\Area\FrontNameResolverInterface');
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
        $this->_model = new \Magento\Framework\App\AreaList(
            $this->objectManagerMock,
            $this->_resolverFactory,
            array('testArea' => array('frontName' => 'testFrontName')),
            $expected
        );

        $actual = $this->_model->getCodeByFrontName('testFrontName');
        $this->assertEquals($expected, $actual);
    }

    public function testGetFrontNameWhenAreaCodeAndFrontNameAreSet()
    {
        $expected = 'testFrontName';
        $this->_model = new \Magento\Framework\App\AreaList(
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
        $this->_model = new \Magento\Framework\App\AreaList(
            $this->objectManagerMock,
            $this->_resolverFactory,
            array(),
            ''
        );

        $actual = $this->_model->getFrontName('testAreaCode');
        $this->assertNull($actual);
    }

    public function testGetCodes()
    {
        $areas = array('area1' => 'value1', 'area2' => 'value2');
        $this->_model = new \Magento\Framework\App\AreaList(
            $this->objectManagerMock, $this->_resolverFactory, $areas, ''
        );

        $expected = array_keys($areas);
        $actual = $this->_model->getCodes();
        $this->assertEquals($expected, $actual);
    }

    public function testGetDefaultRouter()
    {
        $areas = array('area1' => ['router' => 'value1'], 'area2' => 'value2');
        $this->_model = new \Magento\Framework\App\AreaList(
            $this->objectManagerMock, $this->_resolverFactory, $areas, ''
        );

        $this->assertEquals($this->_model->getDefaultRouter('area1'), $areas['area1']['router']);
        $this->assertNull($this->_model->getDefaultRouter('area2'));
    }

    public function testGetArea()
    {
        /** @var \Magento\Framework\ObjectManager $objectManagerMock */
        $objectManagerMock = $this->getObjectManagerMockGetArea();
        $areas = array('area1' => ['router' => 'value1'], 'area2' => 'value2');
        $this->_model = new AreaList(
            $objectManagerMock, $this->_resolverFactory, $areas, ''
        );

        $this->assertEquals($this->_model->getArea('testArea'), 'ok');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getObjectManagerMockGetArea()
    {
        $objectManagerMock = $this->getMock('Magento\Framework\ObjectManager', [], [], '', false);
        $objectManagerMock
            ->expects($this->any())
            ->method('create')
            ->with(
                $this->equalTo('Magento\Framework\App\AreaInterface'),
                $this->equalTo(array('areaCode' => 'testArea'))
            )
            ->will($this->returnValue('ok'));

        return $objectManagerMock;
    }
}
