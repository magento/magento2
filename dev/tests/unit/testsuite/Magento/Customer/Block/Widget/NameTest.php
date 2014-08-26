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

use Magento\Customer\Service\V1\AddressMetadataServiceInterface;
use Magento\Customer\Service\V1\CustomerMetadataServiceInterface;
use Magento\Customer\Service\V1\Data\Customer;
use Magento\Customer\Service\V1\Data\Eav\AttributeMetadata;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Test class for \Magento\Customer\Block\Widget\Name.
 */
class NameTest extends \PHPUnit_Framework_TestCase
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

    const PREFIX_STORE_LABEL = 'Prefix';

    /**#@-*/

    /** @var  \PHPUnit_Framework_MockObject_MockObject | AttributeMetadata */
    private $_attributeMetadata;

    /** @var  \PHPUnit_Framework_MockObject_MockObject | \Magento\Customer\Helper\Data */
    private $_customerHelper;

    /** @var  \PHPUnit_Framework_MockObject_MockObject | \Magento\Framework\Escaper */
    private $_escaper;

    /** @var  Name */
    private $_block;

    /** @var  \PHPUnit_Framework_MockObject_MockObject | CustomerMetadataServiceInterface */
    private $customerMetadataService;

    /** @var \PHPUnit_Framework_MockObject_MockObject|AddressMetadataServiceInterface */
    private $addressMetadataService;

    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $_objectManager;

    public function setUp()
    {
        $this->_objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->_escaper = $this->getMock('Magento\Framework\Escaper', [], [], '', false);
        $context = $this->getMock('Magento\Framework\View\Element\Template\Context', [], [], '', false);
        $context->expects($this->any())->method('getEscaper')->will($this->returnValue($this->_escaper));

        $addressHelper = $this->getMock('Magento\Customer\Helper\Address', [], [], '', false);

        $this->_customerHelper = $this->getMock('Magento\Customer\Helper\Data', [], [], '', false);
        $this->_attributeMetadata = $this->getMock(
            'Magento\Customer\Service\V1\Data\Eav\AttributeMetadata',
            [],
            [],
            '',
            false
        );
        $this->customerMetadataService = $this->getMockBuilder('Magento\Customer\Service\V1\CustomerMetadataService')
            ->disableOriginalConstructor()
            ->getMock();
        $this->customerMetadataService
            ->expects($this->any())
            ->method('getCustomAttributesMetadata')
            ->will($this->returnValue([]));
        $this->customerMetadataService->expects($this->any())
            ->method('getAttributeMetadata')
            ->will($this->returnValue($this->_attributeMetadata));

        $this->addressMetadataService = $this->getMockBuilder('Magento\Customer\Service\V1\AddressMetadataService')
            ->disableOriginalConstructor()
            ->getMock();
        $this->addressMetadataService->expects($this->any())
            ->method('getAttributeMetadata')
            ->will($this->returnValue($this->_attributeMetadata));

        $this->_block = new Name(
            $context,
            $addressHelper,
            $this->customerMetadataService,
            $this->addressMetadataService,
            $this->_customerHelper
        );
    }

    /**
     * @see self::_setUpShowAttribute()
     */
    public function testShowPrefix()
    {
        $this->_setUpShowAttribute([Customer::PREFIX => self::PREFIX]);
        $this->assertTrue($this->_block->showPrefix());

        $this->_attributeMetadata->expects($this->at(0))->method('isVisible')->will($this->returnValue(false));
        $this->assertFalse($this->_block->showPrefix());
    }

    public function testShowPrefixWithException()
    {
        $this->customerMetadataService->expects(
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
        $this->assertFalse($this->_block->showPrefix());
    }

    /**
     * @param $method
     * @dataProvider methodDataProvider
     */
    public function testMethodWithNoSuchEntityException($method)
    {
        $this->customerMetadataService->expects(
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
        $this->assertFalse($this->_block->{$method}());
    }

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
        $this->_setUpShowAttribute(array(Customer::MIDDLENAME => self::MIDDLENAME));
        $this->assertTrue($this->_block->showMiddlename());
    }

    public function testIsMiddlenameRequired()
    {
        $this->_setUpIsAttributeRequired();
        $this->assertTrue($this->_block->isMiddlenameRequired());
    }

    public function testShowSuffix()
    {
        $this->_setUpShowAttribute([Customer::SUFFIX => self::SUFFIX]);
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
        $customer = $this->_objectManager->getObject('Magento\Customer\Service\V1\Data\CustomerBuilder')
            ->setPrefix('  <' . self::PREFIX . '>  ')->create();

        $this->_block->setObject($customer);

        $prefixOptions = ['Mrs' => 'Mrs', 'Ms' => 'Ms', 'Miss' => 'Miss'];

        $prefix = '&lt;' . self::PREFIX . '&gt;';
        $expectedOptions = $prefixOptions;
        $expectedOptions[$prefix] = $prefix;

        $this->_customerHelper->expects(
            $this->once()
        )->method(
            'getNamePrefixOptions'
        )->will(
            $this->returnValue($prefixOptions)
        );
        $this->_escaper->expects($this->once())->method('escapeHtml')->will($this->returnValue($prefix));

        $this->assertSame($expectedOptions, $this->_block->getPrefixOptions());
    }

    public function testGetPrefixOptionsEmpty()
    {
        $customer = $this->_objectManager->getObject('Magento\Customer\Service\V1\Data\CustomerBuilder')
            ->setPrefix(self::PREFIX)->create();
        $this->_block->setObject($customer);

        $this->_customerHelper->expects(
            $this->once()
        )->method(
            'getNamePrefixOptions'
        )->will(
            $this->returnValue([])
        );

        $this->assertEmpty($this->_block->getPrefixOptions());
    }

    public function testGetSuffixOptionsNotEmpty()
    {
        /**
         * Added padding and special characters to show that trim() works on Customer::getSuffix() and that
         * a properly htmlspecialchars translated value is returned.
         */
        $customer = $this->_objectManager->getObject('Magento\Customer\Service\V1\Data\CustomerBuilder')
            ->setSuffix('  <' . self::SUFFIX . '>  ')->create();
        $this->_block->setObject($customer);

        $suffixOptions = ['Sr' => 'Sr'];

        $suffix = '&lt;' . self::SUFFIX . '&gt;';
        $expectedOptions = $suffixOptions;
        $expectedOptions[$suffix] = $suffix;

        $this->_customerHelper->expects(
            $this->once()
        )->method(
            'getNameSuffixOptions'
        )->will(
            $this->returnValue($suffixOptions)
        );
        $this->_escaper->expects($this->once())->method('escapeHtml')->will($this->returnValue($suffix));

        $this->assertSame($expectedOptions, $this->_block->getSuffixOptions());
    }

    public function testGetSuffixOptionsEmpty()
    {
        $customer = $this->_objectManager->getObject('Magento\Customer\Service\V1\Data\CustomerBuilder')
            ->setSuffix('  <' . self::SUFFIX . '>  ')->create();
        $this->_block->setObject($customer);

        $this->_customerHelper->expects(
            $this->once()
        )->method(
            'getNameSuffixOptions'
        )->will(
            $this->returnValue([])
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
        $this->_attributeMetadata->expects(
            $this->at(0)
        )->method(
            'isVisible'
        )->will(
            $this->returnValue($isPrefixVisible)
        );
        $this->_attributeMetadata->expects(
            $this->at(1)
        )->method(
            'isVisible'
        )->will(
            $this->returnValue($isMiddlenameVisible)
        );
        $this->_attributeMetadata->expects(
            $this->at(2)
        )->method(
            'isVisible'
        )->will(
            $this->returnValue($isSuffixVisible)
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
        $this->_attributeMetadata->expects(
            $this->once()
        )->method(
            'getStoreLabel'
        )->will(
            $this->returnValue($storeLabel)
        );
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
        $this->customerMetadataService->expects(
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
        $this->assertSame('', $this->_block->getStoreLabel('attributeCode'));
    }

    /**
     * Helper method for testing all show*() methods.
     *
     * @param array $data Customer attribute(s)
     */
    private function _setUpShowAttribute(array $data)
    {
        $customer = $this->_objectManager->getObject('Magento\Customer\Service\V1\Data\CustomerBuilder')
            ->populateWithArray($data)->create();

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
        $this->_attributeMetadata->expects($this->at(0))->method('isVisible')->will($this->returnValue(true));
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
        $this->_attributeMetadata->expects($this->at(0))->method('isRequired')->will($this->returnValue(false));
        $this->_attributeMetadata->expects($this->at(1))->method('isRequired')->will($this->returnValue(true));
        $this->_attributeMetadata->expects($this->at(2))->method('isRequired')->will($this->returnValue(true));
    }
}
