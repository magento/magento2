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
        self::assertCount(1, $items);

        $document = $items[0];
        self::assertInstanceOf(DocumentInterface::class, $document);
        self::assertEquals($idValue, $document->getId());

        $attributeId = $document->getCustomAttribute($idFieldName);
        self::assertInstanceOf(AttributeInterface::class, $attributeId);
        self::assertEquals($idFieldName, $attributeId->getAttributeCode());
        self::assertEquals($idValue, $attributeId->getValue());

        $attributeIdFieldName = $document->getCustomAttribute('id_field_name');
        self::assertInstanceOf(AttributeInterface::class, $attributeIdFieldName);
        self::assertEquals('id_field_name', $attributeIdFieldName->getAttributeCode());
        self::assertEquals($idFieldName, $attributeIdFieldName->getValue());

        $attributeFoo = $document->getCustomAttribute('attribute_foo');
        self::assertInstanceOf(AttributeInterface::class, $attributeFoo);
        self::assertEquals('attribute_foo', $attributeFoo->getAttributeCode());
        self::assertEquals('attribute_foo_value', $attributeFoo->getValue());

        $attributeBar = $document->getCustomAttribute('attribute_bar');
        self::assertInstanceOf(AttributeInterface::class, $attributeBar);
        self::assertEquals('attribute_bar', $attributeBar->getAttributeCode());
        self::assertEquals('attribute_bar_value', $attributeBar->getValue());

        self::assertEquals($totalCount, $searchResult->getTotalCount());
        self::assertEquals($searchCriteria, $searchResult->getSearchCriteria());
    }
}
