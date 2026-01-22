<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Test\Unit\Model;

use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Catalog\Model\ResourceModel\Helper;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection;
use Magento\ConfigurableProduct\Model\ConfigurableAttributeHandler;
use Magento\ConfigurableProduct\Model\SuggestedAttributeList;
use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SuggestedAttributeListTest extends TestCase
{
    use MockCreationTrait;

    /**
     * @var SuggestedAttributeList
     */
    protected $suggestedListModel;

    /**
     * @var ConfigurableAttributeHandler|MockObject
     */
    protected $configurableAttributeHandler;

    /**
     * @var MockObject
     */
    protected $resourceHelperMock;

    /**
     * @var MockObject
     */
    protected $collectionMock;

    /**
     * @var MockObject
     */
    protected $attributeMock;

    /**
     * @var string
     */
    protected $labelPart = 'labelPart';

    protected function setUp(): void
    {
        $this->configurableAttributeHandler = $this->createMock(
            ConfigurableAttributeHandler::class
        );
        $this->resourceHelperMock = $this->createMock(Helper::class);
        $this->collectionMock = $this->createMock(
            Collection::class
        );
        $this->resourceHelperMock->expects(
            $this->once()
        )->method(
            'addLikeEscape'
        )->with(
            $this->labelPart,
            ['position' => 'any']
        )->willReturn(
            $this->labelPart
        );
        $this->configurableAttributeHandler->expects(
            $this->once()
        )->method(
            'getApplicableAttributes'
        )->willReturn(
            $this->collectionMock
        );
        $valueMap = [
            ['frontend_label', ['like' => $this->labelPart], $this->collectionMock],
        ];
        $this->collectionMock->expects(
            $this->any()
        )->method(
            'addFieldToFilter'
        )->willReturnMap(
            $valueMap
        );
        $this->attributeMock = $this->createPartialMockWithReflection(
            Attribute::class,
            ['getSource', 'getAttributeCode', 'getId', 'getFrontendLabel']
        );
        $this->collectionMock->expects(
            $this->once()
        )->method(
            'getItems'
        )->willReturn(
            ['id' => $this->attributeMock]
        );
        $this->suggestedListModel = new SuggestedAttributeList(
            $this->configurableAttributeHandler,
            $this->resourceHelperMock
        );
    }

    public function testGetSuggestedAttributesIfTheyApplicable()
    {
        $source = $this->createMock(AbstractSource::class);
        $result['id'] = ['id' => 'id', 'label' => 'label', 'code' => 'code', 'options' => 'options'];
        // Configure mock to return expected values
        $this->attributeMock->method('getId')->willReturn('id');
        $this->attributeMock->method('getFrontendLabel')->willReturn('label');
        $this->attributeMock->method('getAttributeCode')->willReturn('code');
        $this->attributeMock->method('getSource')->willReturn($source);
        $source->expects($this->once())->method('getAllOptions')->with(false)->willReturn('options');
        $this->configurableAttributeHandler->expects($this->once())->method('isAttributeApplicable')
            ->with($this->attributeMock)->willReturn(true);

        $this->assertEquals($result, $this->suggestedListModel->getSuggestedAttributes($this->labelPart));
    }

    public function testGetSuggestedAttributesIfTheyNotApplicable()
    {
        // Anonymous class methods are available but not expected to be called in this test
        $this->configurableAttributeHandler->expects($this->once())->method('isAttributeApplicable')
            ->with($this->attributeMock)->willReturn(false);

        $this->assertEquals([], $this->suggestedListModel->getSuggestedAttributes($this->labelPart));
    }
}
