<?php
/**
 * \Magento\Customer\Service\Eav\CustomerMetadataService
 *
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
namespace Magento\Customer\Service\V1;

use Magento\Exception\NoSuchEntityException;

class CustomerMetadataServiceTest extends \PHPUnit_Framework_TestCase
{
    /** Sample values for testing */
    const ATTRIBUTE_CODE = 1;

    const FRONTEND_INPUT = 'select';

    const INPUT_FILTER = 'input filter';

    const STORE_LABEL = 'store label';

    const FRONTEND_CLASS = 'frontend class';

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Eav\Model\Config
     */
    private $_eavConfigMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Eav\Model\Entity\Attribute\AbstractAttribute
     */
    private $_attributeEntityMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
     */
    private $_sourceMock;

    /** @var array */
    private $_validateRules = array();

    public function setUp()
    {
        $this->_eavConfigMock = $this->getMockBuilder(
            '\Magento\Eav\Model\Config'
        )->disableOriginalConstructor()->getMock();

        $this->_attributeEntityMock = $this->getMockBuilder(
            '\Magento\Eav\Model\Entity\Attribute\AbstractAttribute'
        )->setMethods(
            array(
                'getAttributeCode',
                'getFrontendInput',
                'getInputFilter',
                'getStoreLabel',
                'getValidateRules',
                'getSource',
                'getFrontend',
                'usesSource',
                '__wakeup'
            )
        )->disableOriginalConstructor()->getMock();

        $this->_sourceMock = $this->getMockBuilder(
            '\Magento\Eav\Model\Entity\Attribute\Source\AbstractSource'
        )->disableOriginalConstructor()->getMock();

        $frontendMock = $this->getMockBuilder(
            '\Magento\Eav\Model\Entity\Attribute\Frontend\AbstractFrontend'
        )->disableOriginalConstructor()->setMethods(
            array('getClass')
        )->getMock();

        $frontendMock->expects($this->any())->method('getClass')->will($this->returnValue(self::FRONTEND_CLASS));

        $this->_mockReturnValue(
            $this->_attributeEntityMock,
            array(
                'getAttributeCode' => self::ATTRIBUTE_CODE,
                'getFrontendInput' => self::FRONTEND_INPUT,
                'getInputFilter' => self::INPUT_FILTER,
                'getStoreLabel' => self::STORE_LABEL,
                'getValidateRules' => $this->_validateRules,
                'getFrontend' => $frontendMock
            )
        );
    }

    public function testGetAttributeMetadata()
    {
        $this->_eavConfigMock->expects(
            $this->any()
        )->method(
            'getAttribute'
        )->will(
            $this->returnValue($this->_attributeEntityMock)
        );

        $this->_attributeEntityMock->expects($this->any())->method('usesSource')->will($this->returnValue(true));

        $this->_attributeEntityMock->expects(
            $this->any()
        )->method(
            'getSource'
        )->will(
            $this->returnValue($this->_sourceMock)
        );

        $allOptions = array(
            array('label' => 'label1', 'value' => 'value1'),
            array('label' => 'label2', 'value' => 'value2')
        );
        $this->_sourceMock->expects($this->any())->method('getAllOptions')->will($this->returnValue($allOptions));

        $attributeColFactoryMock = $this->getMockBuilder(
            '\Magento\Customer\Model\Resource\Form\Attribute\CollectionFactory'
        )->disableOriginalConstructor()->getMock();
        $storeManagerMock = $this->getMockBuilder(
            '\Magento\Core\Model\StoreManager'
        )->disableOriginalConstructor()->getMock();

        $optionBuilder = new \Magento\Customer\Service\V1\Data\Eav\OptionBuilder();
        $validationRuleBuilder = new \Magento\Customer\Service\V1\Data\Eav\ValidationRuleBuilder();

        $attributeMetadataBuilder = new \Magento\Customer\Service\V1\Data\Eav\AttributeMetadataBuilder(
            $optionBuilder,
            $validationRuleBuilder
        );

        $service = new CustomerMetadataService(
            $this->_eavConfigMock,
            $attributeColFactoryMock,
            $storeManagerMock,
            $optionBuilder,
            $validationRuleBuilder,
            $attributeMetadataBuilder
        );

        $attributeMetadata = $service->getAttributeMetadata('entityCode', 'attributeId');
        $this->assertMetadataAttributes($attributeMetadata);

        $options = $attributeMetadata->getOptions();
        $this->assertNotEquals(array(), $options);
        $this->assertEquals('label1', $options['label1']->getLabel());
        $this->assertEquals('value1', $options['label1']->getValue());
        $this->assertEquals('label2', $options['label2']->getLabel());
        $this->assertEquals('value2', $options['label2']->getValue());
    }

    public function testGetAttributeMetadataWithoutAttributeMetadata()
    {
        $this->_eavConfigMock->expects($this->any())->method('getAttribute')->will($this->returnValue(false));

        $attributeColFactoryMock = $this->getMockBuilder(
            '\Magento\Customer\Model\Resource\Form\Attribute\CollectionFactory'
        )->disableOriginalConstructor()->getMock();
        $storeManagerMock = $this->getMockBuilder(
            '\Magento\Core\Model\StoreManager'
        )->disableOriginalConstructor()->getMock();

        $optionBuilder = new \Magento\Customer\Service\V1\Data\Eav\OptionBuilder();
        $validationRuleBuilder = new \Magento\Customer\Service\V1\Data\Eav\ValidationRuleBuilder();

        $attributeMetadataBuilder = new \Magento\Customer\Service\V1\Data\Eav\AttributeMetadataBuilder(
            $optionBuilder,
            $validationRuleBuilder
        );

        $service = new CustomerMetadataService(
            $this->_eavConfigMock,
            $attributeColFactoryMock,
            $storeManagerMock,
            $optionBuilder,
            $validationRuleBuilder,
            $attributeMetadataBuilder
        );

        try {
            $service->getAttributeMetadata('entityCode', 'attributeId');
            $this->fail('Expected exception not thrown.');
        } catch (\Magento\Exception\NoSuchEntityException $e) {
            $this->assertEquals(\Magento\Exception\NoSuchEntityException::NO_SUCH_ENTITY, $e->getCode());
            $this->assertEquals(
                array('entityType' => 'entityCode', 'attributeCode' => 'attributeId'),
                $e->getParams()
            );
        }
    }

    public function testGetAttributeMetadataWithoutOptions()
    {
        $this->_eavConfigMock->expects(
            $this->any()
        )->method(
            'getAttribute'
        )->will(
            $this->returnValue($this->_attributeEntityMock)
        );

        $this->_attributeEntityMock->expects(
            $this->any()
        )->method(
            'getSource'
        )->will(
            $this->returnValue($this->_sourceMock)
        );

        $this->_sourceMock->expects($this->any())->method('getAllOptions')->will($this->returnValue(array()));

        $attributeColFactoryMock = $this->getMockBuilder(
            '\Magento\Customer\Model\Resource\Form\Attribute\CollectionFactory'
        )->disableOriginalConstructor()->getMock();
        $storeManagerMock = $this->getMockBuilder(
            '\Magento\Core\Model\StoreManager'
        )->disableOriginalConstructor()->getMock();

        $optionBuilder = new \Magento\Customer\Service\V1\Data\Eav\OptionBuilder();
        $validationRuleBuilder = new \Magento\Customer\Service\V1\Data\Eav\ValidationRuleBuilder();

        $attributeMetadataBuilder = new \Magento\Customer\Service\V1\Data\Eav\AttributeMetadataBuilder(
            $optionBuilder,
            $validationRuleBuilder
        );

        $service = new CustomerMetadataService(
            $this->_eavConfigMock,
            $attributeColFactoryMock,
            $storeManagerMock,
            $optionBuilder,
            $validationRuleBuilder,
            $attributeMetadataBuilder
        );

        $attributeMetadata = $service->getAttributeMetadata('entityCode', 'attributeId');
        $this->assertMetadataAttributes($attributeMetadata);

        $options = $attributeMetadata->getOptions();
        $this->assertEquals(0, count($options));
    }

    public function testGetAttributeMetadataWithoutSource()
    {
        $this->_eavConfigMock->expects(
            $this->any()
        )->method(
            'getAttribute'
        )->will(
            $this->returnValue($this->_attributeEntityMock)
        );

        $this->_attributeEntityMock->expects($this->any())->method('usesSource')->will($this->returnValue(false));

        $attributeColFactoryMock = $this->getMockBuilder(
            '\Magento\Customer\Model\Resource\Form\Attribute\CollectionFactory'
        )->disableOriginalConstructor()->getMock();
        $storeManagerMock = $this->getMockBuilder(
            '\Magento\Core\Model\StoreManager'
        )->disableOriginalConstructor()->getMock();

        $optionBuilder = new \Magento\Customer\Service\V1\Data\Eav\OptionBuilder();
        $validationRuleBuilder = new \Magento\Customer\Service\V1\Data\Eav\ValidationRuleBuilder();

        $attributeMetadataBuilder = new \Magento\Customer\Service\V1\Data\Eav\AttributeMetadataBuilder(
            $optionBuilder,
            $validationRuleBuilder
        );

        $service = new CustomerMetadataService(
            $this->_eavConfigMock,
            $attributeColFactoryMock,
            $storeManagerMock,
            $optionBuilder,
            $validationRuleBuilder,
            $attributeMetadataBuilder
        );

        $attributeMetadata = $service->getAttributeMetadata('entityCode', 'attributeId');
        $this->assertMetadataAttributes($attributeMetadata);

        $options = $attributeMetadata->getOptions();
        $this->assertEquals(0, count($options));
    }

    public function testGetCustomerAttributeMetadataWithoutAttributeMetadata()
    {
        $this->_eavConfigMock->expects($this->any())->method('getAttribute')->will($this->returnValue(false));

        $attributeColFactoryMock = $this->getMockBuilder(
            '\Magento\Customer\Model\Resource\Form\Attribute\CollectionFactory'
        )->disableOriginalConstructor()->getMock();
        $storeManagerMock = $this->getMockBuilder(
            '\Magento\Core\Model\StoreManager'
        )->disableOriginalConstructor()->getMock();

        $optionBuilder = new \Magento\Customer\Service\V1\Data\Eav\OptionBuilder();
        $validationRuleBuilder = new \Magento\Customer\Service\V1\Data\Eav\ValidationRuleBuilder();

        $attributeMetadataBuilder = new \Magento\Customer\Service\V1\Data\Eav\AttributeMetadataBuilder(
            $optionBuilder,
            $validationRuleBuilder
        );

        $service = new CustomerMetadataService(
            $this->_eavConfigMock,
            $attributeColFactoryMock,
            $storeManagerMock,
            $optionBuilder,
            $validationRuleBuilder,
            $attributeMetadataBuilder
        );

        try {
            $service->getCustomerAttributeMetadata('attributeId');
            $this->fail('Expected exception not thrown.');
        } catch (NoSuchEntityException $e) {
            $this->assertEquals(NoSuchEntityException::NO_SUCH_ENTITY, $e->getCode());
            $this->assertEquals(array('entityType' => 'customer', 'attributeCode' => 'attributeId'), $e->getParams());
        }
    }

    public function testGetAddressAttributeMetadataWithoutAttributeMetadata()
    {
        $this->_eavConfigMock->expects($this->any())->method('getAttribute')->will($this->returnValue(false));

        $attributeColFactoryMock = $this->getMockBuilder(
            '\Magento\Customer\Model\Resource\Form\Attribute\CollectionFactory'
        )->disableOriginalConstructor()->getMock();
        $storeManagerMock = $this->getMockBuilder(
            '\Magento\Core\Model\StoreManager'
        )->disableOriginalConstructor()->getMock();

        $optionBuilder = new \Magento\Customer\Service\V1\Data\Eav\OptionBuilder();
        $validationRuleBuilder = new \Magento\Customer\Service\V1\Data\Eav\ValidationRuleBuilder();

        $attributeMetadataBuilder = new \Magento\Customer\Service\V1\Data\Eav\AttributeMetadataBuilder(
            $optionBuilder,
            $validationRuleBuilder
        );

        $service = new CustomerMetadataService(
            $this->_eavConfigMock,
            $attributeColFactoryMock,
            $storeManagerMock,
            $optionBuilder,
            $validationRuleBuilder,
            $attributeMetadataBuilder
        );

        try {
            $service->getAddressAttributeMetadata('attributeId');
            $this->fail('Expected exception not thrown.');
        } catch (NoSuchEntityException $e) {
            $this->assertEquals(NoSuchEntityException::NO_SUCH_ENTITY, $e->getCode());
            $this->assertEquals(
                array('entityType' => 'customer_address', 'attributeCode' => 'attributeId'),
                $e->getParams()
            );
        }
    }

    public function testGetAllAttributeSetMetadataWithoutAttributeMetadata()
    {
        $this->_eavConfigMock->expects($this->any())->method('getAttribute')->will($this->returnValue(false));

        $this->_eavConfigMock->expects(
            $this->any()
        )->method(
            'getEntityAttributeCodes'
        )->will(
            $this->returnValue(array('bogus'))
        );

        $attributeColFactoryMock = $this->getMockBuilder(
            '\Magento\Customer\Model\Resource\Form\Attribute\CollectionFactory'
        )->disableOriginalConstructor()->getMock();

        $storeManagerMock = $this->getMockBuilder(
            '\Magento\Core\Model\StoreManager'
        )->disableOriginalConstructor()->getMock();

        $optionBuilder = new \Magento\Customer\Service\V1\Data\Eav\OptionBuilder();
        $validationRuleBuilder = new \Magento\Customer\Service\V1\Data\Eav\ValidationRuleBuilder();

        $attributeMetadataBuilder = new \Magento\Customer\Service\V1\Data\Eav\AttributeMetadataBuilder(
            $optionBuilder,
            $validationRuleBuilder
        );

        $service = new CustomerMetadataService(
            $this->_eavConfigMock,
            $attributeColFactoryMock,
            $storeManagerMock,
            $optionBuilder,
            $validationRuleBuilder,
            $attributeMetadataBuilder
        );

        $this->assertEquals(array(), $service->getAllAttributeSetMetadata('entityType', 0, 1));
    }

    /**
     * @param \PHPUnit_Framework_MockObject_MockObject $mock
     * @param array $valueMap
     */
    private function _mockReturnValue($mock, $valueMap)
    {
        foreach ($valueMap as $method => $value) {
            $mock->expects($this->any())->method($method)->will($this->returnValue($value));
        }
    }

    /**
     * @param $attributeMetadata
     */
    private function assertMetadataAttributes($attributeMetadata)
    {
        $this->assertEquals(self::ATTRIBUTE_CODE, $attributeMetadata->getAttributeCode());
        $this->assertEquals(self::FRONTEND_INPUT, $attributeMetadata->getFrontendInput());
        $this->assertEquals(self::INPUT_FILTER, $attributeMetadata->getInputFilter());
        $this->assertEquals(self::STORE_LABEL, $attributeMetadata->getStoreLabel());
        $this->assertEquals($this->_validateRules, $attributeMetadata->getValidationRules());
        $this->assertEquals(self::FRONTEND_CLASS, $attributeMetadata->getFrontendClass());
    }
}
