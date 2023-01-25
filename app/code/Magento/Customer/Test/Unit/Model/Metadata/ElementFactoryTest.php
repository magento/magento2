<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Model\Metadata;

use Magento\Customer\Model\Attribute\Data\Postcode;
use Magento\Customer\Model\Data\AttributeMetadata;
use Magento\Customer\Model\Metadata\ElementFactory;
use Magento\Customer\Model\Metadata\Form\Text;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Stdlib\StringUtils;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ElementFactoryTest extends TestCase
{
    /** @var ObjectManagerInterface|MockObject */
    private $_objectManager;

    /** @var AttributeMetadata|MockObject */
    private $_attributeMetadata;

    /** @var string */
    private $_entityTypeCode = 'customer_address';

    /** @var ElementFactory */
    private $_elementFactory;

    protected function setUp(): void
    {
        $this->_objectManager = $this->getMockForAbstractClass(ObjectManagerInterface::class);
        $this->_attributeMetadata = $this->createMock(AttributeMetadata::class);
        $this->_elementFactory = new ElementFactory($this->_objectManager, new StringUtils());
    }

    /** TODO fix when Validation is implemented MAGETWO-17341 */
    public function testAttributePostcodeDataModelClass()
    {
        $this->_attributeMetadata->expects(
            $this->once()
        )->method(
            'getDataModel'
        )->willReturn(
            Postcode::class
        );

        $dataModel = $this->createMock(Text::class);
        $this->_objectManager->expects($this->once())->method('create')->willReturn($dataModel);

        $actual = $this->_elementFactory->create($this->_attributeMetadata, '95131', $this->_entityTypeCode);
        $this->assertSame($dataModel, $actual);
    }

    public function testAttributeEmptyDataModelClass()
    {
        $this->_attributeMetadata->expects($this->once())->method('getDataModel')->willReturn('');
        $this->_attributeMetadata->expects(
            $this->once()
        )->method(
            'getFrontendInput'
        )->willReturn(
            'text'
        );

        $dataModel = $this->createMock(Text::class);
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
            Text::class,
            $params
        )->willReturn(
            $dataModel
        );

        $actual = $this->_elementFactory->create($this->_attributeMetadata, 'Some Text', $this->_entityTypeCode);
        $this->assertSame($dataModel, $actual);
    }
}
