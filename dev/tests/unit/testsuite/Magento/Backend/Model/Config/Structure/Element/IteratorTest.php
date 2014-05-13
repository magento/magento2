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
namespace Magento\Backend\Model\Config\Structure\Element;

class IteratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Backend\Model\Config\Structure\Element\Iterator
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_flyweightMock;

    protected function setUp()
    {
        $elementData = array('group1' => array('id' => 1), 'group2' => array('id' => 2), 'group3' => array('id' => 3));
        $this->_flyweightMock = $this->getMock(
            'Magento\Backend\Model\Config\Structure\Element\Group',
            array(),
            array(),
            '',
            false
        );

        $this->_model = new \Magento\Backend\Model\Config\Structure\Element\Iterator($this->_flyweightMock);
        $this->_model->setElements($elementData, 'scope');
    }

    protected function tearDown()
    {
        unset($this->_model);
        unset($this->_flyweightMock);
    }

    public function testIteratorInitializesFlyweight()
    {
        $this->_flyweightMock->expects($this->at(0))->method('setData')->with(array('id' => 1), 'scope');
        $this->_flyweightMock->expects($this->at(2))->method('setData')->with(array('id' => 2), 'scope');
        $this->_flyweightMock->expects($this->at(4))->method('setData')->with(array('id' => 3), 'scope');
        $this->_flyweightMock->expects($this->any())->method('isVisible')->will($this->returnValue(true));
        $counter = 0;
        foreach ($this->_model as $item) {
            $this->assertEquals($this->_flyweightMock, $item);
            $counter++;
        }
        $this->assertEquals(3, $counter);
    }

    public function testIteratorSkipsNonValidElements()
    {
        $this->_flyweightMock->expects($this->exactly(3))->method('isVisible')->will($this->returnValue(false));
        $this->_flyweightMock->expects($this->exactly(3))->method('setData');
        foreach ($this->_model as $item) {
            unset($item);
            $this->fail('Iterator shows non visible fields');
        }
    }

    /**
     * @param string $elementId
     * @param bool $result
     * @dataProvider isLastDataProvider
     */
    public function testIsLast($elementId, $result)
    {
        $elementMock = $this->getMock(
            'Magento\Backend\Model\Config\Structure\Element\Field',
            array(),
            array(),
            '',
            false
        );
        $elementMock->expects($this->once())->method('getId')->will($this->returnValue($elementId));
        $this->assertEquals($result, $this->_model->isLast($elementMock));
    }

    public function isLastDataProvider()
    {
        return array(array(1, false), array(2, false), array(3, true));
    }
}
