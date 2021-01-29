<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Unit\Block\Widget;

use Magento\Customer\Api\Data\AttributeMetadataInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Customer\Block\Widget\Name;

/**
 * Test class for \Magento\Customer\Block\Widget\Name.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class NameTest extends \PHPUnit\Framework\TestCase
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

    /** @var  \PHPUnit\Framework\MockObject\MockObject | AttributeMetadataInterface */
    private $attribute;

    /** @var  \PHPUnit\Framework\MockObject\MockObject | \Magento\Customer\Model\Options */
    private $_options;

    /** @var  \PHPUnit\Framework\MockObject\MockObject | \Magento\Framework\Escaper */
    private $_escaper;

    /** @var  Name */
    private $_block;

    /** @var  \PHPUnit\Framework\MockObject\MockObject | \Magento\Customer\Api\CustomerMetadataInterface */
    private $customerMetadata;

    /** @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Customer\Api\AddressMetadataInterface */
    private $addressMetadata;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $_objectManager;

    protected function setUp(): void
    {
        $this->_objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->_escaper = $this->createMock(\Magento\Framework\Escaper::class);
        $context = $this->createMock(\Magento\Framework\View\Element\Template\Context::class);
        $context->expects($this->any())->method('getEscaper')->willReturn($this->_escaper);

        $addressHelper = $this->createMock(\Magento\Customer\Helper\Address::class);

        $this->_options = $this->createMock(\Magento\Customer\Model\Options::class);

        $this->attribute = $this->getMockBuilder(\Magento\Customer\Api\Data\AttributeMetadataInterface::class)
            ->getMockForAbstractClass();
        $this->customerMetadata = $this->getMockBuilder(\Magento\Customer\Api\CustomerMetadataInterface::class)
            ->getMockForAbstractClass();
        $this->customerMetadata->expects($this->any())
            ->method('getAttributeMetadata')
            ->willReturn($this->attribute);
        $this->customerMetadata
            ->expects($this->any())
            ->method('getCustomAttributesMetadata')
            ->willReturn([]);

        $this->addressMetadata = $this->getMockBuilder(\Magento\Customer\Api\AddressMetadataInterface::class)
            ->getMockForAbstractClass();
        $this->addressMetadata->expects($this->any())
            ->method('getAttributeMetadata')
            ->willReturn($this->attribute);

        $this->_block = new \Magento\Customer\Block\Widget\Name(
            $context,
            $addressHelper,
            $this->customerMetadata,
            $this->_options,
            $this->addressMetadata
        );
    }

    /**
     * @see self::_setUpShowAttribute()
     */
    public function testShowPrefix()
    {
        $this->_setUpShowAttribute([\Magento\Customer\Model\Data\Customer::PREFIX => self::PREFIX]);
        $this->assertTrue($this->_block->showPrefix());

        $this->attribute->expects($this->at(0))->method('isVisible')->willReturn(false);
        $this->assertFalse($this->_block->showPrefix());
    }

    public function testShowPrefixWithException()
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
        $this->assertFalse($this->_block->showPrefix());
    }

    /**
     * @param $method
     * @dataProvider methodDataProvider
     */
    public function testMethodWithNoSuchEntityException($method)
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
        $this->assertFalse($this->_block->{$method}());
    }

    /**
     * @return array
     */
    public function methodDataProvider()
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
     * @see self::_setUpIsAttributeRequired()
     */
    public function testIsPrefixRequired()
    {
        $this->_setUpIsAttributeRequired();
        $this->assertTrue($this->_block->isPrefixRequired());
    }

    public function testShowMiddlename()
    {
        $this->_setUpShowAttribute([\Magento\Customer\Model\Data\Customer::MIDDLENAME => self::MIDDLENAME]);
        $this->assertTrue($this->_block->showMiddlename());
    }

    public function testIsMiddlenameRequired()
    {
        $this->_setUpIsAttributeRequired();
        $this->assertTrue($this->_block->isMiddlenameRequired());
    }

    public function testShowSuffix()
    {
        $this->_setUpShowAttribute([\Magento\Customer\Model\Data\Customer::SUFFIX => self::SUFFIX]);
        $this->assertTrue($this->_block->showSuffix());
    }

    public function testIsSuffixRequired()
    {
        $this->_setUpIsAttributeRequired();
        $this->assertTrue($this->_block->isSuffixRequired());
    }

    public function testGetPrefixOptionsNotEmpty()
    {
        /**
         * Added some padding so that the trim() call on Customer::getPrefix() will remove it. Also added
         * special characters so that the escapeHtml() method returns a htmlspecialchars translated value.
         */
        $customer = $this->getMockBuilder(
            \Magento\Customer\Api\Data\CustomerInterface::class
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

    public function testGetPrefixOptionsEmpty()
    {
        $customer = $this->getMockBuilder(
            \Magento\Customer\Api\Data\CustomerInterface::class
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

    public function testGetSuffixOptionsNotEmpty()
    {
        /**
         * Added padding and special characters to show that trim() works on Customer::getSuffix() and that
         * a properly htmlspecialchars translated value is returned.
         */
        $customer = $this->getMockBuilder(
            \Magento\Customer\Api\Data\CustomerInterface::class
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

    public function testGetSuffixOptionsEmpty()
    {
        $customer = $this->getMockBuilder(
            \Magento\Customer\Api\Data\CustomerInterface::class
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

    public function testGetClassName()
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
     * @dataProvider getContainerClassNameProvider
     */
    public function testGetContainerClassName($isPrefixVisible, $isMiddlenameVisible, $isSuffixVisible, $expectedValue)
    {
        $this->attribute->expects(
            $this->at(0)
        )->method(
            'isVisible'
        )->willReturn(
            $isPrefixVisible
        );
        $this->attribute->expects(
            $this->at(1)
        )->method(
            'isVisible'
        )->willReturn(
            $isMiddlenameVisible
        );
        $this->attribute->expects(
            $this->at(2)
        )->method(
            'isVisible'
        )->willReturn(
            $isSuffixVisible
        );

        $this->assertEquals($expectedValue, $this->_block->getContainerClassName());
    }

    /**
     * This data provider provides enough data sets to test both ternary operator code paths for each one
     * that's used in Name::getContainerClassName().
     *
     * @return array
     */
    public function getContainerClassNameProvider()
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
     * @dataProvider getStoreLabelProvider
     */
    public function testGetStoreLabel($attributeCode, $storeLabel, $expectedValue)
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
    public function getStoreLabelProvider()
    {
        return [
            [self::INVALID_ATTRIBUTE_CODE, '', ''],
            [self::PREFIX_ATTRIBUTE_CODE, self::PREFIX_STORE_LABEL, self::PREFIX_STORE_LABEL]
        ];
    }

    public function testGetStoreLabelWithException()
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
        $this->assertSame('', (string)$this->_block->getStoreLabel('attributeCode'));
    }

    /**
     * Helper method for testing all show*() methods.
     *
     * @param array $data Customer attribute(s)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    private function _setUpShowAttribute(array $data)
    {
        $customer = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)
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
        $this->attribute->expects($this->at(0))->method('isVisible')->willReturn(true);
    }

    /**
     * Helper method for testing all is*Required() methods.
     */
    private function _setUpIsAttributeRequired()
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
        $this->attribute->expects($this->at(0))->method('isRequired')->willReturn(false);
        $this->attribute->expects($this->at(1))->method('isRequired')->willReturn(true);
        $this->attribute->expects($this->at(2))->method('isRequired')->willReturn(true);
    }
}
