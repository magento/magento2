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
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Catalog\Service\V1\Product\Attribute;

use Magento\Catalog\Service\V1\Data\Eav\AttributeMetadata;
use Magento\Catalog\Service\V1\Data\Eav\AttributeMetadataBuilder;
use Magento\Catalog\Service\V1\Product\MetadataService;
use Magento\Catalog\Service\V1\Product\MetadataServiceInterface as ProductMetadataServiceInterface;
use Magento\Catalog\Service\V1\Data\Eav\Product\Attribute\FrontendLabel;

class WriteServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Service\V1\Data\Eav\AttributeMetadataBuilder
     */
    protected $attributeMetadataBuilder;

    /**
     * @var \Magento\Catalog\Model\Resource\Eav\Attribute | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $attributeMock;

    /**
     * @var \Magento\Eav\Model\Adminhtml\System\Config\Source\Inputtype\Validator
     */
    protected $inputValidator;

    /**
     * @var \Magento\Eav\Model\Config | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $eavConfig;

    /**
     * @var FrontendLabel | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $frontendLabelMock;

    /**
     * @var \Magento\Catalog\Service\V1\Product\Attribute\WriteService
     */
    protected $attributeWriteService;

    /**
     * @var int
     */
    protected $typeId = 4;

    public function setUp()
    {
        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->attributeMetadataBuilder = $objectManager
            ->getObject('Magento\Catalog\Service\V1\Data\Eav\AttributeMetadataBuilder');

        $this->attributeMock = $this->getMock('\Magento\Catalog\Model\Resource\Eav\Attribute', [], [], '', false);
        $attributeFactory =
            $this->getMock('\Magento\Catalog\Model\Resource\Eav\AttributeFactory', ['create'], [], '', false);
        $attributeFactory
            ->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->attributeMock));

        $this->eavConfig = $this->getMock('\Magento\Eav\Model\Config', [], [], '', false);
        $this->inputValidator =
            $this->getMock('\Magento\Eav\Model\Adminhtml\System\Config\Source\Inputtype\Validator', [], [], '', false);
        $inputValidatorFactory =
            $this->getMock(
                '\Magento\Eav\Model\Adminhtml\System\Config\Source\Inputtype\ValidatorFactory',
                ['create'], [], '', false
            );
        $inputValidatorFactory
            ->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->inputValidator));

        $this->frontendLabelMock = $this->getMock(
            '\Magento\Catalog\Service\V1\Data\Eav\Product\Attribute\FrontendLabel', [], [], '', false
        );
        $this->frontendLabelMock->expects($this->any())
            ->method('getStoreId')
            ->will($this->returnValue(0));

        $this->frontendLabelMock->expects($this->any())
            ->method('getLabel')
            ->will($this->returnValue('Default Label'));

        $this->attributeWriteService = $objectManager->getObject(
            '\Magento\Catalog\Service\V1\Product\Attribute\WriteService',
            [
                'eavConfig' => $this->eavConfig,
                'attributeFactory' => $attributeFactory,
                'inputtypeValidatorFactory' => $inputValidatorFactory
            ]
        );
    }

    /**
     * @dataProvider createDataProvider
     */
    public function testCreate($attrCode)
    {
        $dataMock = $this->getMock('\Magento\Catalog\Service\V1\Data\Eav\AttributeMetadata', [], [], '', false);
        $dataMock->expects($this->any())->method('getFrontendLabel')
            ->will($this->returnValue([$this->frontendLabelMock]));
        $dataMock->expects($this->any())->method('__toArray')->will($this->returnValue(array()));
        $dataMock->expects($this->any())->method('getAttributeCode')->will($this->returnValue($attrCode));
        $dataMock->expects($this->any())->method('getFrontendInput')->will($this->returnValue('textarea'));
        $this->inputValidator->expects($this->once())->method('isValid')->will($this->returnValue(true));
        $this->attributeMock->expects($this->any())->method('setEntityTypeId')->will($this->returnSelf());
        $this->attributeMock->expects($this->any())->method('save')->will($this->returnSelf());
        $this->eavConfig
            ->expects($this->once())
            ->method('getEntityType')
            ->with(\Magento\Catalog\Model\Product::ENTITY)
            ->will($this->returnValue(new \Magento\Framework\Object()));

        $this->attributeWriteService->create($dataMock);
    }

    /**
     * @return array
     */
    public function createDataProvider()
    {
        return array(
            ['code_111'],
            [''] //cover generateCode()
        );
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     */
    public function testCreateEmptyLabel()
    {
        $dataMock = $this->getMock('\Magento\Catalog\Service\V1\Data\Eav\AttributeMetadata', [], [], '', false);
        $dataMock->expects($this->at(0))->method('getFrontendLabel')->will($this->returnValue([]));
        $this->attributeWriteService->create($dataMock);
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     */
    public function testCreateInvalidCode()
    {
        $dataMock = $this->getMock('\Magento\Catalog\Service\V1\Data\Eav\AttributeMetadata', [], [], '', false);
        $dataMock->expects($this->at(0))->method('__toArray')->will($this->returnValue(array()));
        $dataMock->expects($this->at(1))->method('getFrontendLabel')
            ->will($this->returnValue([$this->frontendLabelMock]));
        $dataMock->expects($this->at(2))->method('getFrontendLabel')
            ->will($this->returnValue([$this->frontendLabelMock]));
        $dataMock->expects($this->at(3))->method('getAttributeCode')->will($this->returnValue('111'));
        $this->attributeWriteService->create($dataMock);
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     */
    public function testCreateInvalidInput()
    {
        $dataMock = $this->getMock('\Magento\Catalog\Service\V1\Data\Eav\AttributeMetadata', [], [], '', false);
        $dataMock->expects($this->at(0))->method('__toArray')->will($this->returnValue(array()));
        $dataMock->expects($this->at(1))->method('getFrontendLabel')
            ->will($this->returnValue([$this->frontendLabelMock]));
        $dataMock->expects($this->at(2))->method('getFrontendLabel')
            ->will($this->returnValue([$this->frontendLabelMock]));
        $dataMock->expects($this->at(3))->method('getAttributeCode')->will($this->returnValue('code_111'));
        $dataMock->expects($this->at(4))->method('getFrontendInput')->will($this->returnValue('textarea'));
        $this->inputValidator->expects($this->at(0))->method('isValid')->will($this->returnValue(false));
        $this->attributeWriteService->create($dataMock);
    }

    public function testUpdate()
    {
        $attributeObject = $this->getMockBuilder('\Magento\Catalog\Service\V1\Data\Eav\AttributeMetadata')
            ->disableOriginalConstructor()
            ->getMock();

        $attributeCode = 'color';
        $this->attributeMock
            ->expects($this->at(0))
            ->method('loadByCode')
            ->with(
                $this->equalTo(\Magento\Catalog\Model\Product::ENTITY),
                $this->equalTo($attributeCode)
            );

        $attributeObject
            ->expects($this->once())
            ->method('__toArray')
            ->will($this->returnValue(
               [
                   AttributeMetadata::FILTERABLE => 2,
                   AttributeMetadata::FRONTEND_LABEL => [
                       [
                           'store_id' => 1,
                           'label'    => 'Label for store 1'
                       ],
                   ],
                   AttributeMetadata::APPLY_TO => [
                       'simple',
                       'virtual'
                   ]
               ]
            ));


        // check that methods will be called
        $this->attributeMock->expects($this->at(1))->method('getId')->will($this->returnValue(1));
        $this->attributeMock->expects($this->at(2))->method('getAttributeId')->will($this->returnValue(1));
        // cover "getIsUserDefined" method - uses __call because "getIsUserDefined" magic method
        $this->attributeMock->expects($this->at(3))->method('__call')->will($this->returnValue(true));
        // cover "getFrontendInput" method - uses __call because "getFrontendInput" magic method
        $this->attributeMock->expects($this->at(4))->method('__call')->will($this->returnValue('select'));
        // cover "getFrontendLabel" method - uses __call because "getFrontendLabel" magic method
        $this->attributeMock->expects($this->at(5))->method('__call')
            ->will($this->returnValue('Label'));
        // cover "getIsUserDefined" method - uses __call because "getIsUserDefined" magic method
        // return false to check unset of element with "apply_to" key
        $this->attributeMock->expects($this->at(6))->method('__call')->will($this->returnValue(false));
        // absent of "apply_to" key also checks here - because of false in previous call
        $this->attributeMock->expects($this->at(7))->method('addData')
            ->with(
                [
                    'filterable'     => 2,
                    'frontend_label' => [0 => 'Label', 1 => 'Label for store 1'],
                    'attribute_id'   => 1,
                    'user_defined'   => true,
                    'frontend_input' => 'select'
                ]
            );

        $this->attributeMock->expects($this->at(8))->method('save');
        $this->attributeMock->expects($this->at(9))->method('getAttributeCode')
            ->will($this->returnValue($attributeCode));

        // run process
        $this->attributeWriteService->update($attributeCode, $attributeObject);
    }

    /**
     * Test for remove attribute
     */
    public function testRemove()
    {
        $id = 1;
        $this->eavConfig
            ->expects($this->once())
            ->method('getAttribute')
            ->with(ProductMetadataServiceInterface::ENTITY_TYPE, $id)
            ->will($this->returnValue($this->attributeMock));
        $this->attributeMock->expects($this->at(0))->method('getId')->will($this->returnValue(1));
        $this->attributeMock->expects($this->at(1))->method('delete');
        $this->assertTrue($this->attributeWriteService->remove($id));
    }

    /**
     * Test for remove attribute
     */
    public function testRemoveNoSuchEntityException()
    {
        $id = -1;
        $this->eavConfig
            ->expects($this->once())
            ->method('getAttribute')
            ->with(ProductMetadataServiceInterface::ENTITY_TYPE, $id)
            ->will($this->returnValue(false));
        $this->setExpectedException(
            'Magento\Framework\Exception\NoSuchEntityException',
            "No such entity with attribute_id = $id"
        );
        $this->assertTrue($this->attributeWriteService->remove($id));
    }
}
