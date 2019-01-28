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
        self::assertSame($idValue, $document->getId());

        $attributeId = $document->getCustomAttribute($idFieldName);
        self::assertInstanceOf(AttributeInterface::class, $attributeId);
        self::assertSame($idFieldName, $attributeId->getAttributeCode());
        self::assertSame($idValue, $attributeId->getValue());

        $attributeIdFieldName = $document->getCustomAttribute('id_field_name');
        self::assertInstanceOf(AttributeInterface::class, $attributeIdFieldName);
        self::assertSame('id_field_name', $attributeIdFieldName->getAttributeCode());
        self::assertSame($idFieldName, $attributeIdFieldName->getValue());

        $attributeFoo = $document->getCustomAttribute('attribute_foo');
        self::assertInstanceOf(AttributeInterface::class, $attributeFoo);
        self::assertSame('attribute_foo', $attributeFoo->getAttributeCode());
        self::assertSame('attribute_foo_value', $attributeFoo->getValue());

        $attributeBar = $document->getCustomAttribute('attribute_bar');
        self::assertInstanceOf(AttributeInterface::class, $attributeBar);
        self::assertSame('attribute_bar', $attributeBar->getAttributeCode());
        self::assertSame('attribute_bar_value', $attributeBar->getValue());

        self::assertSame($totalCount, $searchResult->getTotalCount());
        self::assertSame($searchCriteria, $searchResult->getSearchCriteria());
    }
}
