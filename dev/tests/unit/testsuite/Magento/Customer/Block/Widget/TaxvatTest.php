<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Block\Widget;

use Magento\Framework\Exception\NoSuchEntityException;

class TaxvatTest extends \PHPUnit_Framework_TestCase
{
    /** Constants used in the unit tests */
    const CUSTOMER_ENTITY_TYPE = 'customer';

    const TAXVAT_ATTRIBUTE_CODE = 'taxvat';

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Customer\Api\CustomerMetadataInterface
     */
    private $customerMetadata;

    /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Customer\Api\Data\AttributeMetadataInterface */
    private $attribute;

    /** @var Taxvat */
    private $_block;

    public function setUp()
    {
        $this->attribute = $this->getMockBuilder('\Magento\Customer\Api\Data\AttributeMetadataInterface')
            ->getMockForAbstractClass();
        $this->customerMetadata = $this->getMockBuilder('\Magento\Customer\Api\CustomerMetadataInterface')
            ->getMockForAbstractClass();
        $this->customerMetadata->expects(
            $this->any()
        )->method(
            'getAttributeMetadata'
        )->with(
            self::TAXVAT_ATTRIBUTE_CODE
        )->will(
            $this->returnValue($this->attribute)
        );

        $this->_block = new Taxvat(
            $this->getMock('Magento\Framework\View\Element\Template\Context', [], [], '', false),
            $this->getMock('Magento\Customer\Helper\Address', [], [], '', false),
            $this->customerMetadata
        );
    }

    /**
     * @param bool $isVisible Determines whether the 'taxvat' attribute is visible or enabled
     * @param bool $expectedValue The value we expect from Taxvat::isEnabled()
     * @return void
     *
     * @dataProvider isEnabledDataProvider
     */
    public function testIsEnabled($isVisible, $expectedValue)
    {
        $this->attribute->expects($this->once())->method('isVisible')->will($this->returnValue($isVisible));
        $this->assertSame($expectedValue, $this->_block->isEnabled());
    }

    /**
     * @return array
     */
    public function isEnabledDataProvider()
    {
        return [[true, true], [false, false]];
    }

    public function testIsEnabledWithException()
    {
        $this->customerMetadata->expects(
            $this->any()
        )->method(
            'getAttributeMetadata'
        )->will(
            $this->throwException(new NoSuchEntityException(
                    NoSuchEntityException::MESSAGE_SINGLE_FIELD,
                    ['fieldName' => 'field', 'fieldValue' => 'value']
                )
            )
        );
        $this->assertSame(false, $this->_block->isEnabled());
    }

    /**
     * @param bool $isRequired Determines whether the 'taxvat' attribute is required
     * @param bool $expectedValue The value we expect from Taxvat::isRequired()
     * @return void
     *
     * @dataProvider isRequiredDataProvider
     */
    public function testIsRequired($isRequired, $expectedValue)
    {
        $this->attribute->expects($this->once())->method('isRequired')->will($this->returnValue($isRequired));
        $this->assertSame($expectedValue, $this->_block->isRequired());
    }

    /**
     * @return array
     */
    public function isRequiredDataProvider()
    {
        return [[true, true], [false, false]];
    }

    public function testIsRequiredWithException()
    {
        $this->customerMetadata->expects(
            $this->any()
        )->method(
            'getAttributeMetadata'
        )->will(
            $this->throwException(new NoSuchEntityException(
                    NoSuchEntityException::MESSAGE_SINGLE_FIELD,
                    ['fieldName' => 'field', 'fieldValue' => 'value']
                )
            )
        );
        $this->assertSame(false, $this->_block->isRequired());
    }
}
