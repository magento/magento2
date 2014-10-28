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
namespace Magento\Backend\Block\Widget\Grid\Column\Renderer\Radio;

class ExtendedTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Extended
     */
    protected $_object;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_converter;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_column;

    protected function setUp()
    {
        $context = $this->getMock('\Magento\Backend\Block\Context', array(), array(), '', false);
        $this->_converter = $this->getMock(
            '\Magento\Backend\Block\Widget\Grid\Column\Renderer\Options\Converter',
            array('toFlatArray'),
            array(),
            '',
            false
        );
        $this->_column = $this->getMock(
            'Magento\Backend\Block\Widget\Grid\Column',
            array('getValues', 'getIndex', 'getHtmlName'),
            array(),
            '',
            false
        );
        $this->_object = new Extended($context, $this->_converter);
        $this->_object->setColumn($this->_column);
    }

    /**
     * @param array $rowData
     * @param string $expectedResult
     * @dataProvider renderDataProvider
     */
    public function testRender(array $rowData, $expectedResult)
    {
        $selectedFlatArray = array(1 => 'One');
        $this->_column->expects($this->once())->method('getValues')->will($this->returnValue($selectedFlatArray));
        $this->_column->expects($this->once())->method('getIndex')->will($this->returnValue('label'));
        $this->_column->expects($this->once())->method('getHtmlName')->will($this->returnValue('test[]'));
        $this->_converter->expects($this->never())->method('toFlatArray');
        $this->assertEquals($expectedResult, $this->_object->render(new \Magento\Framework\Object($rowData)));
    }

    public function renderDataProvider()
    {
        return array(
            'checked' => array(
                array('id' => 1, 'label' => 'One'),
                '<input type="radio" name="test[]" value="1" class="radio" checked="checked"/>'
            ),
            'not checked' => array(
                array('id' => 2, 'label' => 'Two'),
                '<input type="radio" name="test[]" value="2" class="radio"/>'
            )
        );
    }
}
