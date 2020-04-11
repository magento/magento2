<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Custom import CSV file field for shipping table rates
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\OfflineShipping\Test\Unit\Block\Adminhtml\Form\Field;

use Magento\Framework\Data\Form;
use Magento\Framework\Escaper;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\OfflineShipping\Block\Adminhtml\Form\Field\Import;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ImportTest extends TestCase
{
    /**
     * @var Import
     */
    protected $_object;

    /**
     * @var MockObject
     */
    protected $_formMock;

    protected function setUp(): void
    {
        $this->_formMock = $this->createPartialMock(
            Form::class,
            ['getFieldNameSuffix', 'addSuffixToName', 'getHtmlIdPrefix', 'getHtmlIdSuffix']
        );
        $testData = ['name' => 'test_name', 'html_id' => 'test_html_id'];
        $testHelper = new ObjectManager($this);
        $this->_object = $testHelper->getObject(
            Import::class,
            [
                'data' => $testData,
                '_escaper' => $testHelper->getObject(Escaper::class)
            ]
        );
        $this->_object->setForm($this->_formMock);
    }

    public function testGetNameWhenFormFiledNameSuffixIsEmpty()
    {
        $this->_formMock->expects($this->once())->method('getFieldNameSuffix')->will($this->returnValue(false));
        $this->_formMock->expects($this->never())->method('addSuffixToName');
        $actual = $this->_object->getName();
        $this->assertEquals('test_name', $actual);
    }

    public function testGetNameWhenFormFiledNameSuffixIsNotEmpty()
    {
        $this->_formMock->expects($this->once())->method('getFieldNameSuffix')->will($this->returnValue(true));
        $this->_formMock->expects($this->once())->method('addSuffixToName')->will($this->returnValue('test_suffix'));
        $actual = $this->_object->getName();
        $this->assertEquals('test_suffix', $actual);
    }

    public function testGetElementHtml()
    {
        $this->_formMock->expects(
            $this->any()
        )->method(
            'getHtmlIdPrefix'
        )->will(
            $this->returnValue('test_name_prefix')
        );
        $this->_formMock->expects(
            $this->any()
        )->method(
            'getHtmlIdSuffix'
        )->will(
            $this->returnValue('test_name_suffix')
        );
        $testString = $this->_object->getElementHtml();
        $this->assertStringStartsWith(
            '<input id="time_condition" type="hidden" name="test_name" value="',
            $testString
        );
        $this->assertStringEndsWith(
            '<input id="test_name_prefixtest_html_idtest_name_suffix" ' .
            'name="test_name"  data-ui-id="form-element-test_name" value="" type="file"/>',
            $testString
        );
    }
}
