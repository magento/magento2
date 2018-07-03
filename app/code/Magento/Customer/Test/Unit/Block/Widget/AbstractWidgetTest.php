<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Block\Widget;

use Magento\Customer\Block\Widget\AbstractWidget;

class AbstractWidgetTest extends \PHPUnit\Framework\TestCase
{
    /** Constants used in the various unit tests. */
    const KEY_FIELD_ID_FORMAT = 'field_id_format';

    const KEY_FIELD_NAME_FORMAT = 'field_name_format';

    const FORMAT_D = '%d';

    const FORMAT_S = '%s';

    /** @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Customer\Helper\Address */
    private $_addressHelper;

    /** @var AbstractWidget */
    private $_block;

    protected function setUp()
    {
        $this->_addressHelper = $this->createMock(\Magento\Customer\Helper\Address::class);

        $this->_block = new \Magento\Customer\Block\Widget\AbstractWidget(
            $this->createMock(\Magento\Framework\View\Element\Template\Context::class),
            $this->_addressHelper,
            $this->getMockBuilder(\Magento\Customer\Api\CustomerMetadataInterface::class)->getMockForAbstractClass()
        );
    }

    /**
     * @param string $key
     * @param string|null $expectedValue
     *
     * @dataProvider getConfigDataProvider
     */
    public function testGetConfig($key, $expectedValue)
    {
        $this->_addressHelper->expects(
            $this->once()
        )->method(
            'getConfig'
        )->with(
            $key
        )->will(
            $this->returnValue($expectedValue)
        );
        $this->assertEquals($expectedValue, $this->_block->getConfig($key));
    }

    /**
     * @return array
     */
    public function getConfigDataProvider()
    {
        return [['key', 'value'], [null, null]];
    }

    /**
     * The default field id format is '%s' so we set it to '%d' to verify that '%d' gets returned when
     * AbstractWidget::getFieldIdFormat() is called.
     */
    public function testGetFieldIdFormatHasData()
    {
        $this->_block->setData(self::KEY_FIELD_ID_FORMAT, self::FORMAT_D);
        $this->assertEquals(self::FORMAT_D, $this->_block->getFieldIdFormat());
    }

    /**
     * Returns the default '%s' field id format when the block has no data for it.
     */
    public function testGetFieldIdFormatHasNoData()
    {
        $this->assertEquals(self::FORMAT_S, $this->_block->getFieldIdFormat());
    }

    /**
     * The default field name format is '%s' so we set it to '%d' to verify that '%d' gets returned when
     * AbstractWidget::getFieldNameFormat() is called.
     */
    public function testGetFieldNameFormatHasData()
    {
        $this->_block->setData(self::KEY_FIELD_NAME_FORMAT, self::FORMAT_D);
        $this->assertEquals(self::FORMAT_D, $this->_block->getFieldNameFormat());
    }

    /**
     * Returns the default '%s' field name format when the block has no data for it.
     */
    public function testGetFieldNameFormatHasNoData()
    {
        $this->assertEquals(self::FORMAT_S, $this->_block->getFieldNameFormat());
    }

    /**
     * Test '%s' and '%d' formats to verify that '%s' returns a string and '%d' returns a numeric
     * string when AbstractWidget::getFieldId() is invoked.
     *
     * @param string $format Field id format (e.g. '%s' or '%d')
     * @param string $fieldId Field id
     * @param string $expectedValue The value we expect from AbstractWidget::getFieldId()
     * @param string $method The method to invoke on the result from getFieldId() should return true
     *
     * @dataProvider getFieldIdDataProvider
     */
    public function testGetFieldId($format, $fieldId, $expectedValue, $method)
    {
        $this->_block->setData(self::KEY_FIELD_ID_FORMAT, $format);
        $this->assertTrue(call_user_func($method, $blockFieldId = $this->_block->getFieldId($fieldId)));
        $this->assertSame($expectedValue, $blockFieldId);
    }

    /**
     * @return array
     */
    public function getFieldIdDataProvider()
    {
        return [
            [self::FORMAT_S, 'Id', 'Id', 'is_string'],
            [self::FORMAT_D, '123', '123', 'is_numeric'],
            [self::FORMAT_D, 'Id', '0', 'is_numeric']
        ];
    }

    /**
     * Test '%s' and '%d' formats to verify that '%s' returns a string and '%d' returns a numeric
     * string when AbstractWidget::getFieldName() is invoked.
     *
     * @param string $format Field name format (e.g. '%s' or '%d')
     * @param string $fieldName The field name
     * @param string $expectedValue The value we expect from AbstractWidget::getFieldName
     * @param string $method The method to invoke on the result from getFieldName() should return true
     *
     * @dataProvider getFieldNameDataProvider
     */
    public function testGetFieldName($format, $fieldName, $expectedValue, $method)
    {
        $this->_block->setData(self::KEY_FIELD_NAME_FORMAT, $format);
        $this->assertTrue(call_user_func($method, $blockFieldName = $this->_block->getFieldName($fieldName)));
        $this->assertEquals($expectedValue, $blockFieldName);
    }

    /**
     * @return array
     */
    public function getFieldNameDataProvider()
    {
        return [
            [self::FORMAT_S, 'Name', 'Name', 'is_string'],
            [self::FORMAT_D, '123', '123', 'is_numeric'],
            [self::FORMAT_D, 'Name', '0', 'is_numeric']
        ];
    }
}
