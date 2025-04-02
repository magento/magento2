<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Block\Widget;

use Magento\Customer\Api\AddressMetadataInterface;
use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Api\Data\AttributeMetadataInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Block\Widget\Name;
use Magento\Customer\Helper\Address;
use Magento\Customer\Model\Data\Customer;
use Magento\Customer\Model\Options;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Element\Template\Context;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test class for \Magento\Customer\Block\Widget\Name.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class NameTest extends TestCase
{
    /**#@+
     * Constant values used throughout the various unit tests.
     */
    const PREFIX = 'Mr';

    const MIDDLENAME = 'Middle';

    const SUFFIX = 'Jr';

    const KEY_CLASS_NAME = 'class_name';

    const DEFAULT_CLASS_NAME = 'customer-name';

    const CUSTOM_CLASS_NAME = 'my-class-name';

    const CONTAINER_CLASS_NAME_PREFIX = '-prefix';

    const CONTAINER_CLASS_NAME_MIDDLENAME = '-middlename';

    const CONTAINER_CLASS_NAME_SUFFIX = '-suffix';

    const PREFIX_ATTRIBUTE_CODE = 'prefix';

    const INVALID_ATTRIBUTE_CODE = 'invalid attribute code';

    const PREFIX_STORE_LABEL = 'Name Prefix';
    /**#@-*/

    /**
     * @var MockObject|AttributeMetadataInterface
     */
    private $attribute;

    /**
     * @var MockObject|Options
     */
    private $_options;

    /**
     * @var MockObject|Escaper
     */
    private $_escaper;

    /**
     * @var Name
     */
    private $_block;

    /**
     * @var MockObject|CustomerMetadataInterface
     */
    private $customerMetadata;

    /**
     * @var MockObject|AddressMetadataInterface
     */
    private $addressMetadata;

    /**
     * @var ObjectManager
     */
    protected $_objectManager;

    /**
     * @var bool
     */
    private $isVisibleAttribute = true;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->_objectManager = new ObjectManager($this);
        $this->_escaper = $this->createMock(Escaper::class);
        $context = $this->createMock(Context::class);
        $context->expects($this->any())->method('getEscaper')->willReturn($this->_escaper);

        $addressHelper = $this->createMock(Address::class);

        $this->_options = $this->createMock(Options::class);

        $this->attribute = $this->getMockBuilder(AttributeMetadataInterface::class)
            ->getMockForAbstractClass();
        $this->customerMetadata = $this->getMockBuilder(CustomerMetadataInterface::class)
            ->getMockForAbstractClass();
        $this->customerMetadata->expects($this->any())
            ->method('getAttributeMetadata')
            ->willReturn($this->attribute);
        $this->customerMetadata
            ->expects($this->any())
            ->method('getCustomAttributesMetadata')
            ->willReturn([]);

        $this->addressMetadata = $this->getMockBuilder(AddressMetadataInterface::class)
            ->getMockForAbstractClass();
        $this->addressMetadata->expects($this->any())
            ->method('getAttributeMetadata')
            ->willReturn($this->attribute);

        $this->_block = new Name(
            $context,
            $addressHelper,
            $this->customerMetadata,
            $this->_options,
            $this->addressMetadata
        );
    }

    /**
     * @return void
     * @see self::_setUpShowAttribute()
     */
    public function testShowPrefix(): void
    {
        $this->_setUpShowAttribute([Customer::PREFIX => self::PREFIX]);
        $this->assertTrue($this->_block->showPrefix());

        $this->isVisibleAttribute = false;
        $this->assertFalse($this->_block->showPrefix());
        $this->isVisibleAttribute = true;
    }

    /**
     * @return void
     */
    public function testShowPrefixWithException(): void
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
        $this->assertFalse($this->_block->showPrefix());
    }

    /**
     * @param $method
     *
     * @return void
     * @dataProvider methodDataProvider
     */
    public function testMethodWithNoSuchEntityException($method): void
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
        $this->assertFalse($this->_block->{$method}());
    }

    /**
     * @return array
     */
    public static function methodDataProvider(): array
    {
        return [
            'showPrefix' => ['showPrefix'],
            'isPrefixRequired' => ['isPrefixRequired'],
            'showMiddlename' => ['showMiddlename'],
            'isMiddlenameRequired' => ['isMiddlenameRequired'],
            'showSuffix' => ['showSuffix'],
            'isSuffixRequired' => ['isSuffixRequired']
        ];
    }

    /**
     * @return void
     * @see self::_setUpIsAttributeRequired()
     */
    public function testIsPrefixRequired(): void
    {
        $this->_setUpIsAttributeRequired();
        $this->assertTrue($this->_block->isPrefixRequired());
    }

    /**
     * @return void
     */
    public function testShowMiddlename(): void
    {
        $this->_setUpShowAttribute([Customer::MIDDLENAME => self::MIDDLENAME]);
        $this->assertTrue($this->_block->showMiddlename());
    }

    /**
     * @return void
     */
    public function testIsMiddlenameRequired(): void
    {
        $this->_setUpIsAttributeRequired();
        $this->assertTrue($this->_block->isMiddlenameRequired());
    }

    /**
     * @return void
     */
    public function testShowSuffix(): void
    {
        $this->_setUpShowAttribute([Customer::SUFFIX => self::SUFFIX]);
        $this->assertTrue($this->_block->showSuffix());
    }

    /**
     * @return void
     */
    public function testIsSuffixRequired(): void
    {
        $this->_setUpIsAttributeRequired();
        $this->assertTrue($this->_block->isSuffixRequired());
    }

    /**
     * @return void
     */
    public function testGetPrefixOptionsNotEmpty(): void
    {
        /**
         * Added some padding so that the trim() call on Customer::getPrefix() will remove it. Also added
         * special characters so that the escapeHtml() method returns a htmlspecialchars translated value.
         */
        $customer = $this->getMockBuilder(
            CustomerInterface::class
        )->getMockForAbstractClass();
        $customer->expects($this->once())->method('getPrefix')->willReturn('  <' . self::PREFIX . '>  ');

        $this->_block->setObject($customer);

        $prefixOptions = ['Mrs' => 'Mrs', 'Ms' => 'Ms', 'Miss' => 'Miss'];

        $prefix = '&lt;' . self::PREFIX . '&gt;';
        $expectedOptions = $prefixOptions;
        $expectedOptions[$prefix] = $prefix;

        $this->_options->expects(
            $this->once()
        )->method(
            'getNamePrefixOptions'
        )->willReturn(
            $prefixOptions
        );
        $this->_escaper->expects($this->once())->method('escapeHtml')->willReturn($prefix);

        $this->assertSame($expectedOptions, $this->_block->getPrefixOptions());
    }

    /**
     * @return void
     */
    public function testGetPrefixOptionsEmpty(): void
    {
        $customer = $this->getMockBuilder(
            CustomerInterface::class
        )->getMockForAbstractClass();
        $this->_block->setObject($customer);

        $this->_options->expects(
            $this->once()
        )->method(
            'getNamePrefixOptions'
        )->willReturn(
            []
        );

        $this->assertEmpty($this->_block->getPrefixOptions());
    }

    /**
     * @return void
     */
    public function testGetSuffixOptionsNotEmpty(): void
    {
        /**
         * Added padding and special characters to show that trim() works on Customer::getSuffix() and that
         * a properly htmlspecialchars translated value is returned.
         */
        $customer = $this->getMockBuilder(
            CustomerInterface::class
        )->getMockForAbstractClass();
        $customer->expects($this->once())->method('getSuffix')->willReturn('  <' . self::SUFFIX . '>  ');
        $this->_block->setObject($customer);

        $suffixOptions = ['Sr' => 'Sr'];

        $suffix = '&lt;' . self::SUFFIX . '&gt;';
        $expectedOptions = $suffixOptions;
        $expectedOptions[$suffix] = $suffix;

        $this->_options->expects(
            $this->once()
        )->method(
            'getNameSuffixOptions'
        )->willReturn(
            $suffixOptions
        );
        $this->_escaper->expects($this->once())->method('escapeHtml')->willReturn($suffix);

        $this->assertSame($expectedOptions, $this->_block->getSuffixOptions());
    }

    /**
     * @return void
     */
    public function testGetSuffixOptionsEmpty(): void
    {
        $customer = $this->getMockBuilder(
            CustomerInterface::class
        )->getMockForAbstractClass();
        $this->_block->setObject($customer);

        $this->_options->expects(
            $this->once()
        )->method(
            'getNameSuffixOptions'
        )->willReturn(
            []
        );

        $this->assertEmpty($this->_block->getSuffixOptions());
    }

    /**
     * @return void
     */
    public function testGetClassName(): void
    {
        /** Test the default case when the block has no data set for the class name. */
        $this->assertEquals(self::DEFAULT_CLASS_NAME, $this->_block->getClassName());

        /** Set custom data for the class name and verify that the Name::getClassName() method returns it. */
        $this->_block->setData(self::KEY_CLASS_NAME, self::CUSTOM_CLASS_NAME);
        $this->assertEquals(self::CUSTOM_CLASS_NAME, $this->_block->getClassName());
    }

    /**
     * @param bool $isPrefixVisible Value returned by Name::showPrefix()
     * @param bool $isMiddlenameVisible Value returned by Name::showMiddlename()
     * @param bool $isSuffixVisible Value returned by Name::showSuffix()
     * @param string $expectedValue The expected value of Name::getContainerClassName()
     *
     * @return void
     * @dataProvider getContainerClassNameProvider
     */
    public function testGetContainerClassName(
        $isPrefixVisible,
        $isMiddlenameVisible,
        $isSuffixVisible,
        $expectedValue
    ): void {
        $this->attribute
            ->method('isVisible')
            ->willReturnOnConsecutiveCalls($isPrefixVisible, $isMiddlenameVisible, $isSuffixVisible);

        $this->assertEquals($expectedValue, $this->_block->getContainerClassName());
    }

    /**
     * This data provider provides enough data sets to test both ternary operator code paths for each one
     * that's used in Name::getContainerClassName().
     *
     * @return array
     */
    public static function getContainerClassNameProvider(): array
    {
        return [
            [false, false, false, self::DEFAULT_CLASS_NAME],
            [true, false, false, self::DEFAULT_CLASS_NAME . self::CONTAINER_CLASS_NAME_PREFIX],
            [false, true, false, self::DEFAULT_CLASS_NAME . self::CONTAINER_CLASS_NAME_MIDDLENAME],
            [false, false, true, self::DEFAULT_CLASS_NAME . self::CONTAINER_CLASS_NAME_SUFFIX],
            [
                true,
                true,
                true,
                self::DEFAULT_CLASS_NAME .
                self::CONTAINER_CLASS_NAME_PREFIX .
                self::CONTAINER_CLASS_NAME_MIDDLENAME .
                self::CONTAINER_CLASS_NAME_SUFFIX
            ]
        ];
    }

    /**
     * @param string $attributeCode An attribute code
     * @param string $storeLabel The attribute's store label
     * @param string $expectedValue The expected value of Name::getStoreLabel()
     *
     * @return void
     * @dataProvider getStoreLabelProvider
     */
    public function testGetStoreLabel($attributeCode, $storeLabel, $expectedValue): void
    {
        $this->attribute->expects($this->atLeastOnce())->method('getStoreLabel')->willReturn($storeLabel);
        $this->assertEquals($expectedValue, $this->_block->getStoreLabel($attributeCode));
    }

    /**
     * This data provider provides two data sets. One tests that an empty string is returned for an invalid
     * attribute code instead of an exception being thrown. The second tests that the correct store label is
     * returned for a valid attribute code.
     *
     * @return array
     */
    public static function getStoreLabelProvider(): array
    {
        return [
            [self::INVALID_ATTRIBUTE_CODE, '', ''],
            [self::PREFIX_ATTRIBUTE_CODE, self::PREFIX_STORE_LABEL, self::PREFIX_STORE_LABEL]
        ];
    }

    /**
     * @return void
     */
    public function testGetStoreLabelWithException(): void
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
        $this->assertSame('', (string)$this->_block->getStoreLabel('attributeCode'));
    }

    /**
     * Helper method for testing all show*() methods.
     *
     * @param array $data Customer attribute(s)
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    private function _setUpShowAttribute(array $data): void
    {
        $customer = $this->getMockBuilder(CustomerInterface::class)
            ->getMockForAbstractClass();

        /**
         * These settings cause the first code path in Name::_getAttribute() to be executed, which
         * basically just returns the value of parent::_getAttribute().
         */
        $this->_block->setForceUseCustomerAttributes(true);
        $this->_block->setObject($customer);

        /**
         * The show*() methods return true for the attribute returned by parent::_getAttribute() for the
         * first call to the method. Subsequent calls may return true or false depending on the returnValue
         * of the at({0, 1, 2, 3, ...}), etc. calls as set and configured in a particular test.
         */
        $testClass = $this;
        $this->attribute
            ->method('isVisible')
            ->willReturnCallback(function () use ($testClass) {
                return $testClass->isVisibleAttribute;
            });
    }

    /**
     * Helper method for testing all is*Required() methods.
     *
     * @return void
     */
    private function _setUpIsAttributeRequired(): void
    {
        /**
         * These settings cause the first code path in Name::_getAttribute() to be skipped so that the rest of
         * the code in the other code path(s) can be executed.
         */
        $this->_block->setForceUseCustomerAttributes(false);
        $this->_block->setForceUseCustomerRequiredAttributes(true);
        $this->_block->setObject(new \StdClass());

        /**
         * The first call to isRequired() is false so that the second if conditional in the other code path
         * of Name::_getAttribute() will evaluate to true, which causes the if's code block to be executed.
         * The second isRequired() call causes the code in the nested if conditional to be executed. Thus,
         * all code paths in Name::_getAttribute() will be executed. Returning true for the third isRequired()
         * call causes the is*Required() method of the block to return true for the attribute.
         */
        $this->attribute
            ->method('isRequired')
            ->willReturnOnConsecutiveCalls(false, true, true);
    }
}
