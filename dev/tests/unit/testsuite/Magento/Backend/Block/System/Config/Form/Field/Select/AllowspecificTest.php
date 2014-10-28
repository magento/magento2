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
namespace Magento\Backend\Block\System\Config\Form\Field\Select;

class AllowspecificTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Backend\Block\System\Config\Form\Field\Select\Allowspecific
     */
    protected $_object;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_formMock;

    protected function setUp()
    {
        $testHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->_object = $testHelper->getObject('Magento\Backend\Block\System\Config\Form\Field\Select\Allowspecific');
        $this->_object->setData('html_id', 'spec_element');
        $this->_formMock = $this->getMock(
            'Magento\Framework\Data\Form',
            array('getHtmlIdPrefix', 'getHtmlIdSuffix', 'getElement'),
            array(),
            '',
            false,
            false
        );
    }

    public function testGetAfterElementHtml()
    {
        $this->_formMock->expects(
            $this->exactly(2)
        )->method(
            'getHtmlIdPrefix'
        )->will(
            $this->returnValue('test_prefix_')
        );
        $this->_formMock->expects(
            $this->exactly(2)
        )->method(
            'getHtmlIdSuffix'
        )->will(
            $this->returnValue('_test_suffix')
        );

        $afterHtmlCode = 'after html';
        $this->_object->setData('after_element_html', $afterHtmlCode);
        $this->_object->setForm($this->_formMock);

        $actual = $this->_object->getAfterElementHtml();

        $this->assertStringEndsWith($afterHtmlCode, $actual);
        $this->assertStringStartsWith('<script type="text/javascript">', trim($actual));
        $this->assertContains('test_prefix_spec_element_test_suffix', $actual);
    }

    /**
     * @param $value
     * @dataProvider getHtmlWhenValueIsEmptyDataProvider
     */
    public function testGetHtmlWhenValueIsEmpty($value)
    {
        $this->_object->setForm($this->_formMock);

        $elementMock = $this->getMock(
            'Magento\Framework\Data\Form\Element\Select',
            array('setDisabled'),
            array(),
            '',
            false,
            false
        );

        $elementMock->expects($this->once())->method('setDisabled')->with('disabled');
        $countryId = 'tetst_county_specificcountry';
        $this->_object->setId('tetst_county_allowspecific');
        $this->_formMock->expects(
            $this->once()
        )->method(
            'getElement'
        )->with(
            $countryId
        )->will(
            $this->returnValue($elementMock)
        );

        $this->_object->setValue($value);
        $this->assertNotEmpty($this->_object->getHtml());
    }

    public function getHtmlWhenValueIsEmptyDataProvider()
    {
        return array(
            'zero' => array('1' => 0),
            'null' => array('1' => null),
            'false' => array('1' => false),
            'negative' => array('1' => -1)
        );
    }
}
