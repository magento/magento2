<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Block\Widget;

use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\AttributeMetadataInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Block\Widget\Gender;
use Magento\Customer\Helper\Address;
use Magento\Customer\Model\Session;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template\Context;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GenderTest extends TestCase
{
    /** Constants used in the unit tests */
    const CUSTOMER_ENTITY_TYPE = 'customer';

    const GENDER_ATTRIBUTE_CODE = 'gender';

    /**
     * @var MockObject|CustomerMetadataInterface
     */
    private $customerMetadata;

    /** @var MockObject|AttributeMetadataInterface */
    private $attribute;

    /** @var MockObject|Session */
    private $customerSession;

    /** @var MockObject|CustomerRepositoryInterface */
    private $customerRepository;

    /** @var Gender */
    private $block;

    protected function setUp(): void
    {
        $this->attribute = $this->getMockBuilder(AttributeMetadataInterface::class)
            ->getMockForAbstractClass();

        $this->customerMetadata = $this->getMockBuilder(CustomerMetadataInterface::class)
            ->getMockForAbstractClass();
        $this->customerMetadata->expects($this->any())
            ->method('getAttributeMetadata')
            ->with(self::GENDER_ATTRIBUTE_CODE)
            ->willReturn($this->attribute);

        $this->customerRepository = $this
            ->getMockBuilder(CustomerRepositoryInterface::class)
            ->getMockForAbstractClass();
        $this->customerSession = $this->createMock(Session::class);

        $this->block = new Gender(
            $this->createMock(Context::class),
            $this->createMock(Address::class),
            $this->customerMetadata,
            $this->customerRepository,
            $this->customerSession
        );
    }

    /**
     * Test the Gender::isEnabled() method.
     *
     * @param bool $isVisible Determines whether the 'gender' attribute is visible or enabled
     * @param bool $expectedValue The value we expect from Gender::isEnabled()
     * @return void
     *
     * @dataProvider isEnabledDataProvider
     */
    public function testIsEnabled($isVisible, $expectedValue)
    {
        $this->attribute->expects($this->once())->method('isVisible')->willReturn($isVisible);
        $this->assertSame($expectedValue, $this->block->isEnabled());
    }

    /**
     * The testIsEnabled data provider.
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
        $this->assertFalse($this->block->isEnabled());
    }

    /**
     * Test the Gender::isRequired() method.
     *
     * @param bool $isRequired Determines whether the 'gender' attribute is required
     * @param bool $expectedValue The value we expect from Gender::isRequired()
     * @return void
     *
     * @dataProvider isRequiredDataProvider
     */
    public function testIsRequired($isRequired, $expectedValue)
    {
        $this->attribute->expects($this->once())->method('isRequired')->willReturn($isRequired);
        $this->assertSame($expectedValue, $this->block->isRequired());
    }

    /**
     * The testIsRequired data provider.
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
        $this->assertFalse($this->block->isRequired());
    }

    /**
     * Test the Gender::getCustomer() method.
     * @return void
     */
    public function testGetCustomer()
    {
        $customerData = $this->getMockBuilder(CustomerInterface::class)
            ->getMockForAbstractClass();
        $this->customerSession->expects($this->once())->method('getCustomerId')->willReturn(1);
        $this->customerRepository
            ->expects($this->once())
            ->method('getById')
            ->with(1)
            ->willReturn($customerData);

        $customer = $this->block->getCustomer();
        $this->assertSame($customerData, $customer);
    }

    /**
     * Test the Gender::getGenderOptions() method.
     * @return void
     */
    public function testGetGenderOptions()
    {
        $options = [
            ['label' => __('Male'), 'value' => 'M'],
            ['label' => __('Female'), 'value' => 'F'],
            ['label' => __('Not Specified'), 'value' => 'NA']
        ];

        $this->attribute->expects($this->once())->method('getOptions')->willReturn($options);
        $this->assertSame($options, $this->block->getGenderOptions());
    }
}
