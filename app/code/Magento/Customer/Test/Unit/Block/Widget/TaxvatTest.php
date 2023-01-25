<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Block\Widget;

use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Api\Data\AttributeMetadataInterface;
use Magento\Customer\Block\Widget\Taxvat;
use Magento\Customer\Helper\Address;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template\Context;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class TaxvatTest extends TestCase
{
    /** Constants used in the unit tests */
    const CUSTOMER_ENTITY_TYPE = 'customer';

    const TAXVAT_ATTRIBUTE_CODE = 'taxvat';

    /**
     * @var MockObject|CustomerMetadataInterface
     */
    private $customerMetadata;

    /** @var MockObject|AttributeMetadataInterface */
    private $attribute;

    /** @var Taxvat */
    private $_block;

    protected function setUp(): void
    {
        $this->attribute = $this->getMockBuilder(AttributeMetadataInterface::class)
            ->getMockForAbstractClass();
        $this->customerMetadata = $this->getMockBuilder(CustomerMetadataInterface::class)
            ->getMockForAbstractClass();
        $this->customerMetadata->expects(
            $this->any()
        )->method(
            'getAttributeMetadata'
        )->with(
            self::TAXVAT_ATTRIBUTE_CODE
        )->willReturn(
            $this->attribute
        );

        $this->_block = new Taxvat(
            $this->createMock(Context::class),
            $this->createMock(Address::class),
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
        $this->attribute->expects($this->once())->method('isVisible')->willReturn($isVisible);
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
        )->willThrowException(
            new NoSuchEntityException(
                __(
                    'No such entity with %fieldName = %fieldValue',
                    ['fieldName' => 'field', 'fieldValue' => 'value']
                )
            )
        );
        $this->assertFalse($this->_block->isEnabled());
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
        $this->attribute->expects($this->once())->method('isRequired')->willReturn($isRequired);
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
        )->willThrowException(
            new NoSuchEntityException(
                __(
                    'No such entity with %fieldName = %fieldValue',
                    ['fieldName' => 'field', 'fieldValue' => 'value']
                )
            )
        );
        $this->assertFalse($this->_block->isRequired());
    }
}
