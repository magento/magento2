<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Unit\Block\Widget;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Customer\Block\Widget\Taxvat;

class TaxvatTest extends \PHPUnit\Framework\TestCase
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

    protected function setUp()
    {
        $this->attribute = $this->getMockBuilder(\Magento\Customer\Api\Data\AttributeMetadataInterface::class)
            ->getMockForAbstractClass();
        $this->customerMetadata = $this->getMockBuilder(\Magento\Customer\Api\CustomerMetadataInterface::class)
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

        $this->_block = new \Magento\Customer\Block\Widget\Taxvat(
            $this->createMock(\Magento\Framework\View\Element\Template\Context::class),
            $this->createMock(\Magento\Customer\Helper\Address::class),
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
                __(
                    'No such entity with %fieldName = %fieldValue',
                    ['fieldName' => 'field', 'fieldValue' => 'value']
                )
            ))
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
                __(
                    'No such entity with %fieldName = %fieldValue',
                    ['fieldName' => 'field', 'fieldValue' => 'value']
                )
            ))
        );
        $this->assertSame(false, $this->_block->isRequired());
    }
}
