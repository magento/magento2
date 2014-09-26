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

use Magento\Framework\Exception\NoSuchEntityException;

class CustomerMetadataServiceTest extends \PHPUnit_Framework_TestCase
{
    /** Sample values for testing */
    const ATTRIBUTE_CODE = 1;

    const FRONTEND_INPUT = 'select';

    const INPUT_FILTER = 'input filter';

    const STORE_LABEL = 'store label';

    const FRONTEND_CLASS = 'frontend class';

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Customer\Service\V1\Data\Eav\AttributeMetadataDataProvider
     */
    protected $attributeMetadataDataProvider;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Customer\Service\V1\Data\Eav\AttributeMetadataConverter
     */
    protected $attributeMetadataConverter;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Eav\Model\Entity\Attribute\AbstractAttribute
     */
    protected $attributeEntityMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Customer\Service\V1\Data\Eav\AttributeMetadata
     */
    protected $attributeMetadataMock;

    /** @var array */
    protected $validationRules = array();

    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $objectManager;

    /** @var \Magento\Customer\Service\V1\CustomerMetadataServiceInterface */
    protected $service;

    public function setUp()
    {
        $this->attributeMetadataDataProvider = $this
            ->getMockBuilder('Magento\Customer\Service\V1\Data\Eav\AttributeMetadataDataProvider')
            ->disableOriginalConstructor()
            ->getMock();

        $this->attributeMetadataConverter = $this
            ->getMockBuilder('Magento\Customer\Service\V1\Data\Eav\AttributeMetadataConverter')
            ->disableOriginalConstructor()
            ->getMock();

        $this->attributeMetadataMock = $this
            ->getMockBuilder('Magento\Customer\Service\V1\Data\Eav\AttributeMetadata')
            ->disableOriginalConstructor()
            ->setMethods(
                array(
                    'getAttributeCode',
                    'getFrontendInput',
                    'getInputFilter',
                    'getStoreLabel',
                    'getValidationRules',
                    'getSource',
                    'getFrontendClass',
                    'usesSource'
                )
            )
            ->getMock();

        $this->attributeEntityMock = $this->getMockBuilder(
            '\Magento\Eav\Model\Entity\Attribute\AbstractAttribute'
        )->setMethods(
            array(
                'getId',
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

        $this->_mockReturnValue(
            $this->attributeMetadataMock,
            array(
                'getAttributeCode' => self::ATTRIBUTE_CODE,
                'getFrontendInput' => self::FRONTEND_INPUT,
                'getInputFilter' => self::INPUT_FILTER,
                'getStoreLabel' => self::STORE_LABEL,
                'getValidationRules' => $this->validationRules,
                'getFrontendClass' => self::FRONTEND_CLASS
            )
        );

        $this->objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->service = $this->objectManager->getObject(
            'Magento\Customer\Service\V1\CustomerMetadataService',
            [
                'attributeMetadataDataProvider' => $this->attributeMetadataDataProvider,
                'attributeMetadataConverter' => $this->attributeMetadataConverter
            ]
        );
    }

    public function testGetAttributeMetadata()
    {
        $this->attributeMetadataDataProvider
            ->expects($this->any())
            ->method('getAttribute')
            ->will($this->returnValue($this->attributeEntityMock));

        $this->attributeEntityMock
            ->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(1));

        $this->attributeMetadataConverter
            ->expects($this->any())
            ->method('createMetadataAttribute')
            ->will($this->returnValue($this->attributeMetadataMock));

        $attributeMetadata = $this->service->getAttributeMetadata('attributeId');
        $this->assertMetadataAttributes($attributeMetadata);
    }

    public function testGetAttributeMetadataWithoutAttributeMetadata()
    {
        $this->attributeMetadataDataProvider
            ->expects($this->any())
            ->method('getAttribute')
            ->will($this->returnValue(false));

        try {
            $this->service->getAttributeMetadata('attributeId');
            $this->fail('Expected exception not thrown.');
        } catch (NoSuchEntityException $e) {
            $this->assertSame(
                "No such entity with entityType = customer, attributeCode = attributeId",
                $e->getMessage()
            );
        }
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
        $this->assertEquals($this->validationRules, $attributeMetadata->getValidationRules());
        $this->assertEquals(self::FRONTEND_CLASS, $attributeMetadata->getFrontendClass());
    }
}
