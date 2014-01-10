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

use Magento\Customer\Service\V1\CustomerMetadataService;
use Magento\Customer\Service\V1\Dto\Eav\AttributeMetadata;
use Magento\Customer\Service\V1\Dto\Eav\Option;

class CustomerMetadataServiceTest extends \PHPUnit_Framework_TestCase
{
    /** Sample values for testing */
    const ATTRIBUTE_CODE = 1;
    const FRONTEND_INPUT = 'frontend input';
    const INPUT_FILTER = 'input filter';
    const STORE_LABEL = 'store label';
    const VALIDATE_RULES = 'validate rules';

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

    public function setUp()
    {
        $this->_eavConfigMock = $this->getMockBuilder('\Magento\Eav\Model\Config')
            ->disableOriginalConstructor()
            ->getMock();

        $this->_attributeEntityMock =
            $this->getMockBuilder('\Magento\Eav\Model\Entity\Attribute\AbstractAttribute')
                ->setMethods(
                    array(
                        'getAttributeCode',
                        'getFrontendInput',
                        'getInputFilter',
                        'getStoreLabel',
                        'getValidateRules',
                        'getSource',
                        '__wakeup',
                    )
                )
                ->disableOriginalConstructor()
                ->getMock();

        $this->_sourceMock =
            $this->getMockBuilder('\Magento\Eav\Model\Entity\Attribute\Source\AbstractSource')
                ->disableOriginalConstructor()
                ->getMock();

        $this->_mockReturnValue(
            $this->_attributeEntityMock,
            array(
                'getAttributeCode' => self::ATTRIBUTE_CODE,
                'getFrontendInput' => self::FRONTEND_INPUT,
                'getInputFilter' => self::INPUT_FILTER,
                'getStoreLabel' => self::STORE_LABEL,
                'getValidateRules' => self::VALIDATE_RULES,
            )
        );
    }

    public function testGetAttributeMetadata()
    {
        $this->_eavConfigMock->expects($this->any())
            ->method('getAttribute')
            ->will($this->returnValue($this->_attributeEntityMock));

        $this->_attributeEntityMock->expects($this->any())
            ->method('getSource')
            ->will($this->returnValue($this->_sourceMock));

        $allOptions = array(
            array(
                'label' => 'label1',
                'value' => 'value1',
            ),
            array(
                'label' => 'label2',
                'value' => 'value2',
            ),
        );
        $this->_sourceMock->expects($this->any())
            ->method('getAllOptions')
            ->will($this->returnValue($allOptions));

        $attributeColMock = $this->getMockBuilder('\\Magento\\Customer\\Model\\Resource\\Form\\Attribute\\Collection')
            ->disableOriginalConstructor()
            ->getMock();
        $storeManagerMock = $this->getMockBuilder('\\Magento\\Core\\Model\\StoreManager')
            ->disableOriginalConstructor()
            ->getMock();

        $optionBuilder = new \Magento\Customer\Service\V1\Dto\Eav\OptionBuilder();

        $attributeMetadataBuilder = new \Magento\Customer\Service\V1\Dto\Eav\AttributeMetadataBuilder();

        $service = new CustomerMetadataService($this->_eavConfigMock, $attributeColMock, $storeManagerMock,
            $optionBuilder, $attributeMetadataBuilder);

        $attributeMetadata = $service->getAttributeMetadata('entityCode', 'attributeId');
        $this->assertEquals(self::ATTRIBUTE_CODE, $attributeMetadata->getAttributeCode());
        $this->assertEquals(self::FRONTEND_INPUT, $attributeMetadata->getFrontendInput());
        $this->assertEquals(self::INPUT_FILTER, $attributeMetadata->getInputFilter());
        $this->assertEquals(self::STORE_LABEL, $attributeMetadata->getStoreLabel());
        $this->assertEquals(self::VALIDATE_RULES, $attributeMetadata->getValidationRules());

        $options = $attributeMetadata->getOptions();
        $this->assertNotEquals(array(), $options);
        $this->assertEquals('label1', $options['label1']->getLabel());
        $this->assertEquals('value1', $options['label1']->getValue());
        $this->assertEquals('label2', $options['label2']->getLabel());
        $this->assertEquals('value2', $options['label2']->getValue());
    }

    public function testGetAttributeMetadataWithoutOptions()
    {
        $this->_eavConfigMock->expects($this->any())
            ->method('getAttribute')
            ->will($this->returnValue($this->_attributeEntityMock));

        $this->_attributeEntityMock->expects($this->any())
            ->method('getSource')
            ->will($this->returnValue($this->_sourceMock));

        $this->_sourceMock->expects($this->any())
            ->method('getAllOptions')
            ->will($this->returnValue(array()));

        $attributeColMock = $this->getMockBuilder('\\Magento\\Customer\\Model\\Resource\\Form\\Attribute\\Collection')
            ->disableOriginalConstructor()
            ->getMock();
        $storeManagerMock = $this->getMockBuilder('\\Magento\\Core\\Model\\StoreManager')
            ->disableOriginalConstructor()
            ->getMock();

        $attributeMetadataBuilder = new \Magento\Customer\Service\V1\Dto\Eav\AttributeMetadataBuilder();

        $optionBuilder = new \Magento\Customer\Service\V1\Dto\Eav\OptionBuilder();

        $service = new CustomerMetadataService($this->_eavConfigMock, $attributeColMock, $storeManagerMock,
            $optionBuilder, $attributeMetadataBuilder);

        $attributeMetadata = $service->getAttributeMetadata('entityCode', 'attributeId');
        $this->assertEquals(self::ATTRIBUTE_CODE, $attributeMetadata->getAttributeCode());
        $this->assertEquals(self::FRONTEND_INPUT, $attributeMetadata->getFrontendInput());
        $this->assertEquals(self::INPUT_FILTER, $attributeMetadata->getInputFilter());
        $this->assertEquals(self::STORE_LABEL, $attributeMetadata->getStoreLabel());
        $this->assertEquals(self::VALIDATE_RULES, $attributeMetadata->getValidationRules());

        $options = $attributeMetadata->getOptions();
        $this->assertEquals(0, count($options));
    }

    public function testGetAttributeMetadataWithoutSource()
    {
        $this->_eavConfigMock->expects($this->any())
            ->method('getAttribute')
            ->will($this->returnValue($this->_attributeEntityMock));

        $this->_attributeEntityMock->expects($this->any())
            ->method('getSource')
            ->will($this->throwException(new \Exception('exception message')));

        $attributeColMock = $this->getMockBuilder('\\Magento\\Customer\\Model\\Resource\\Form\\Attribute\\Collection')
            ->disableOriginalConstructor()
            ->getMock();
        $storeManagerMock = $this->getMockBuilder('\\Magento\\Core\\Model\\StoreManager')
            ->disableOriginalConstructor()
            ->getMock();

        $optionBuilder = new \Magento\Customer\Service\V1\Dto\Eav\OptionBuilder();

        $attributeMetadataBuilder = new \Magento\Customer\Service\V1\Dto\Eav\AttributeMetadataBuilder();

        $service = new CustomerMetadataService($this->_eavConfigMock, $attributeColMock, $storeManagerMock,
            $optionBuilder, $attributeMetadataBuilder);

        $attributeMetadata = $service->getAttributeMetadata('entityCode', 'attributeId');
        $this->assertEquals(self::ATTRIBUTE_CODE, $attributeMetadata->getAttributeCode());
        $this->assertEquals(self::FRONTEND_INPUT, $attributeMetadata->getFrontendInput());
        $this->assertEquals(self::INPUT_FILTER, $attributeMetadata->getInputFilter());
        $this->assertEquals(self::STORE_LABEL, $attributeMetadata->getStoreLabel());
        $this->assertEquals(self::VALIDATE_RULES, $attributeMetadata->getValidationRules());

        $options = $attributeMetadata->getOptions();
        $this->assertEquals(0, count($options));
    }

    /**
     * @param \PHPUnit_Framework_MockObject_MockObject $mock
     * @param array $valueMap
     */
    private function _mockReturnValue($mock, $valueMap)
    {
        foreach ($valueMap as $method => $value) {
            $mock->expects($this->any())
                ->method($method)
                ->will($this->returnValue($value));
        }
    }
}
