<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Test\Unit\Model;

class SuggestedAttributeListTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\ConfigurableProduct\Model\SuggestedAttributeList
     */
    protected $suggestedListModel;

    /**
     * @var \Magento\ConfigurableProduct\Model\ConfigurableAttributeHandler|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $configurableAttributeHandler;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $resourceHelperMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $collectionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $attributeMock;

    /**
     * @var string
     */
    protected $labelPart = 'labelPart';

    protected function setUp()
    {
        $this->configurableAttributeHandler = $this->createMock(
            \Magento\ConfigurableProduct\Model\ConfigurableAttributeHandler::class
        );
        $this->resourceHelperMock = $this->createMock(\Magento\Catalog\Model\ResourceModel\Helper::class);
        $this->collectionMock = $this->createMock(
            \Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection::class
        );
        $this->resourceHelperMock->expects(
            $this->once()
        )->method(
            'addLikeEscape'
        )->with(
            $this->labelPart,
            ['position' => 'any']
        )->will(
            $this->returnValue($this->labelPart)
        );
        $this->configurableAttributeHandler->expects(
            $this->once()
        )->method(
            'getApplicableAttributes'
        )->will(
            $this->returnValue($this->collectionMock)
        );
        $valueMap = [
            ['frontend_label', ['like' => $this->labelPart], $this->collectionMock],
        ];
        $this->collectionMock->expects(
            $this->any()
        )->method(
            'addFieldToFilter'
        )->will(
            $this->returnValueMap($valueMap)
        );
        $this->attributeMock = $this->createPartialMock(
            \Magento\Catalog\Model\ResourceModel\Eav\Attribute::class,
            ['getId', 'getFrontendLabel', 'getAttributeCode', 'getSource']
        );
        $this->collectionMock->expects(
            $this->once()
        )->method(
            'getItems'
        )->will(
            $this->returnValue(['id' => $this->attributeMock])
        );
        $this->suggestedListModel = new \Magento\ConfigurableProduct\Model\SuggestedAttributeList(
            $this->configurableAttributeHandler,
            $this->resourceHelperMock
        );
    }

    public function testGetSuggestedAttributesIfTheyApplicable()
    {
        $source = $this->createMock(\Magento\Eav\Model\Entity\Attribute\Source\AbstractSource::class);
        $result['id'] = ['id' => 'id', 'label' => 'label', 'code' => 'code', 'options' => 'options'];
        $this->attributeMock->expects($this->once())->method('getId')->will($this->returnValue('id'));
        $this->attributeMock->expects($this->once())->method('getFrontendLabel')->will($this->returnValue('label'));
        $this->attributeMock->expects($this->once())->method('getAttributeCode')->will($this->returnValue('code'));
        $this->attributeMock->expects($this->once())->method('getSource')->will($this->returnValue($source));
        $source->expects($this->once())->method('getAllOptions')->with(false)->will($this->returnValue('options'));
        $this->configurableAttributeHandler->expects($this->once())->method('isAttributeApplicable')
            ->with($this->attributeMock)->willReturn(true);

        $this->assertEquals($result, $this->suggestedListModel->getSuggestedAttributes($this->labelPart));
    }

    public function testGetSuggestedAttributesIfTheyNotApplicable()
    {
        $this->attributeMock->expects($this->never())->method('getId');
        $this->attributeMock->expects($this->never())->method('getFrontendLabel');
        $this->attributeMock->expects($this->never())->method('getAttributeCode');
        $this->attributeMock->expects($this->never())->method('getSource');
        $this->configurableAttributeHandler->expects($this->once())->method('isAttributeApplicable')
            ->with($this->attributeMock)->willReturn(false);

        $this->assertEquals([], $this->suggestedListModel->getSuggestedAttributes($this->labelPart));
    }
}
