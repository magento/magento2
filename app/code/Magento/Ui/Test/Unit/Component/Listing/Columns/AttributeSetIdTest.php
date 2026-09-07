<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types = 1);

namespace Magento\Ui\Test\Unit\Component\Listing\Columns;

use Magento\Catalog\Ui\Component\Listing\Columns\AttributeSetId;
use Magento\Framework\DB\Select;
use Magento\Framework\View\Element\UiComponent\DataProvider\DataProviderInterface;
use Magento\Eav\Model\Entity\Collection\AbstractCollection;

/**
 * Testing for the AttributeSetID UI column
 */
class AttributeSetIdTest extends ColumnTest
{
    /**
     * @var string
     */
    protected $columnClass = AttributeSetId::class;

    /**
     * @inheritDoc
     */
    public function testPrepare()
    {
        $collectionMock = $this->createMock(AbstractCollection::class);

        $selectMock = $this->createMock(Select::class);

        $selectMock->expects($this->once())
            ->method('order')
            ->with('attribute_set_name asc');

        $this->dataProviderMock = $this->createPartialMockWithReflection(
            DataProviderInterface::class,
            [
                'getName', 'getConfigData', 'setConfigData', 'getMeta', 'getFieldMetaInfo',
                'getFieldSetMetaInfo', 'getFieldsMetaInfo', 'getPrimaryFieldName',
                'getRequestFieldName', 'getData', 'addFilter', 'addOrder', 'setLimit',
                'getSearchCriteria', 'getSearchResult', 'getCollection', 'getSelect'
            ]
        );

        $this->dataProviderMock->expects($this->once())
            ->method('getCollection')
            ->willReturn($collectionMock);

        $collectionMock->expects($this->once())
            ->method('getSelect')
            ->willReturn($selectMock);

        parent::testPrepare();
    }
}
