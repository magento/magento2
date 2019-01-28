<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\DataProvider;

use Magento\Framework\Api\AttributeInterface;
use Magento\Framework\Api\Search\DocumentInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class SearchResultFactoryTest extends TestCase
{
    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var SearchResultFactory
     */
    private $searchResultFactory;

    public function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();

        $this->searchCriteriaBuilder = $objectManager->create(SearchCriteriaBuilder::class);
        $this->searchResultFactory = $objectManager->create(SearchResultFactory::class);
    }

    public function testCreate()
    {
        $idFieldName = 'id';
        $idValue = 15;
        $entities = [
            new EntityFake($idValue, 'attribute_foo_value', 'attribute_bar_value'),
        ];
        $totalCount = 10;
        $searchCriteria = $this->searchCriteriaBuilder->create();

        $searchResult = $this->searchResultFactory->create($entities, $totalCount, $searchCriteria, $idFieldName);
        $items = $searchResult->getItems();
        $this->assertCount(1, $items);

        $document = $items[0];
        $this->assertInstanceOf(DocumentInterface::class, $document);
        $this->assertEquals($idValue, $document->getId());

        $attributeId = $document->getCustomAttribute($idFieldName);
        $this->assertInstanceOf(AttributeInterface::class, $attributeId);
        $this->assertEquals($idFieldName, $attributeId->getAttributeCode());
        $this->assertEquals($idValue, $attributeId->getValue());

        $attributeIdFieldName = $document->getCustomAttribute('id_field_name');
        $this->assertInstanceOf(AttributeInterface::class, $attributeIdFieldName);
        $this->assertEquals('id_field_name', $attributeIdFieldName->getAttributeCode());
        $this->assertEquals($idFieldName, $attributeIdFieldName->getValue());

        $attributeFoo = $document->getCustomAttribute('attribute_foo');
        $this->assertInstanceOf(AttributeInterface::class, $attributeFoo);
        $this->assertEquals('attribute_foo', $attributeFoo->getAttributeCode());
        $this->assertEquals('attribute_foo_value', $attributeFoo->getValue());

        $attributeBar = $document->getCustomAttribute('attribute_bar');
        $this->assertInstanceOf(AttributeInterface::class, $attributeBar);
        $this->assertEquals('attribute_bar', $attributeBar->getAttributeCode());
        $this->assertEquals('attribute_bar_value', $attributeBar->getValue());

        $this->assertEquals($totalCount, $searchResult->getTotalCount());
        $this->assertEquals($searchCriteria, $searchResult->getSearchCriteria());
    }
}
