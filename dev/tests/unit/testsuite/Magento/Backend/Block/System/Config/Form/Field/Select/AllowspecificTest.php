<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
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
            ['getHtmlIdPrefix', 'getHtmlIdSuffix', 'getElement'],
            [],
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
            ['setDisabled'],
            [],
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
        return [
            'zero' => ['1' => 0],
            'null' => ['1' => null],
            'false' => ['1' => false],
            'negative' => ['1' => -1]
        ];
    }
}
