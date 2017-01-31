<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Model\Metadata;

use Magento\Customer\Model\Metadata\ElementFactory;

class ElementFactoryTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Framework\ObjectManagerInterface | \PHPUnit_Framework_MockObject_MockObject */
    private $_objectManager;

    /** @var \Magento\Customer\Model\Data\AttributeMetadata | \PHPUnit_Framework_MockObject_MockObject */
    private $_attributeMetadata;

    /** @var string */
    private $_entityTypeCode = 'customer_address';

    /** @var ElementFactory */
    private $_elementFactory;

    protected function setUp()
    {
        $this->_objectManager = $this->getMock('Magento\Framework\ObjectManagerInterface');
        $this->_attributeMetadata = $this->getMock(
            'Magento\Customer\Model\Data\AttributeMetadata',
            [],
            [],
            '',
            false
        );
        $this->_elementFactory = new ElementFactory($this->_objectManager, new \Magento\Framework\Stdlib\StringUtils());
    }

    /** TODO fix when Validation is implemented MAGETWO-17341 */
    public function testAttributePostcodeDataModelClass()
    {
        $this->_attributeMetadata->expects(
            $this->once()
        )->method(
            'getDataModel'
        )->will(
            $this->returnValue('Magento\Customer\Model\Attribute\Data\Postcode')
        );

        $dataModel = $this->getMock('Magento\Customer\Model\Metadata\Form\Text', [], [], '', false);
        $this->_objectManager->expects($this->once())->method('create')->will($this->returnValue($dataModel));

        $actual = $this->_elementFactory->create($this->_attributeMetadata, '95131', $this->_entityTypeCode);
        $this->assertSame($dataModel, $actual);
    }

    public function testAttributeEmptyDataModelClass()
    {
        $this->_attributeMetadata->expects($this->once())->method('getDataModel')->will($this->returnValue(''));
        $this->_attributeMetadata->expects(
            $this->once()
        )->method(
            'getFrontendInput'
        )->will(
            $this->returnValue('text')
        );

        $dataModel = $this->getMock('Magento\Customer\Model\Metadata\Form\Text', [], [], '', false);
        $params = [
            'entityTypeCode' => $this->_entityTypeCode,
            'value' => 'Some Text',
            'isAjax' => false,
            'attribute' => $this->_attributeMetadata,
        ];
        $this->_objectManager->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            'Magento\Customer\Model\Metadata\Form\Text',
            $params
        )->will(
            $this->returnValue($dataModel)
        );

        $actual = $this->_elementFactory->create($this->_attributeMetadata, 'Some Text', $this->_entityTypeCode);
        $this->assertSame($dataModel, $actual);
    }
}
