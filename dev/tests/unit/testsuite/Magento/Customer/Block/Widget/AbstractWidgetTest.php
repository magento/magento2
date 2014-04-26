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
namespace Magento\Customer\Block\Widget;

class AbstractWidgetTest extends \PHPUnit_Framework_TestCase
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

    public function setUp()
    {
        $this->_addressHelper = $this->getMock('Magento\Customer\Helper\Address', array(), array(), '', false);

        $this->_block = new AbstractWidget(
            $this->getMock('Magento\Framework\View\Element\Template\Context', array(), array(), '', false),
            $this->_addressHelper,
            $this->getMockForAbstractClass(
                'Magento\Customer\Service\V1\CustomerMetadataServiceInterface',
                array(),
                '',
                false
            )
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
        return array(array('key', 'value'), array(null, null));
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
        return array(
            array(self::FORMAT_S, 'Id', 'Id', 'is_string'),
            array(self::FORMAT_D, '123', '123', 'is_numeric'),
            array(self::FORMAT_D, 'Id', '0', 'is_numeric')
        );
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
        return array(
            array(self::FORMAT_S, 'Name', 'Name', 'is_string'),
            array(self::FORMAT_D, '123', '123', 'is_numeric'),
            array(self::FORMAT_D, 'Name', '0', 'is_numeric')
        );
    }
}
