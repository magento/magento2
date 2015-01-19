<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Model\Search;

use Magento\Catalog\Model\Resource\Product\Attribute\CollectionFactory;

class RequestGeneratorTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\TestFramework\Helper\ObjectManager */
    protected $objectManagerHelper;

    /** @var \Magento\CatalogSearch\Model\Search\RequestGenerator */
    protected $object;

    /** @var  CollectionFactory | \PHPUnit_Framework_MockObject_MockObject */
    protected $productAttributeCollectionFactory;

    public function setUp()
    {
        $this->productAttributeCollectionFactory =
            $this->getMockBuilder('Magento\Catalog\Model\Resource\Product\Attribute\CollectionFactory')
                ->setMethods(['create'])
                ->disableOriginalConstructor()
                ->getMock();

        $this->objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->object = $this->objectManagerHelper->getObject(
            'Magento\\CatalogSearch\\Model\\Search\\RequestGenerator',
            ['productAttributeCollectionFactory' => $this->productAttributeCollectionFactory]
        );
    }

    /**
     * @return array
     */
    public function attributesProvider()
    {
        return [
            [[2, 0, 0], 'sku', 'static'],
            [[0, 0, 0], 'price', 'static'],
            [[3, 2, 0], 'name', 'text'],
            [[1, 0, 0], 'name2', 'text', false],
            [[3, 2, 1], 'date', 'decimal'],
            [[3, 2, 1], 'attr_int', 'int'],
        ];
    }

    /**
     * @param array $countResult
     * @param string $code
     * @param string $type
     * @param bool $visibleInAdvanced
     * @dataProvider attributesProvider
     */
    public function testGenerate($countResult, $code, $type, $visibleInAdvanced = true)
    {
        $collection = $this->getMockBuilder('Magento\Catalog\Model\Resource\Product\Attribute\Collection')
            ->disableOriginalConstructor()
            ->getMock();
        $collection->expects($this->any())
            ->method('getIterator')
            ->willReturn(
                new \ArrayIterator(
                    [
                        $this->createAttributeMock($code, $type, $visibleInAdvanced),
                    ]
                )
            );
        $collection->expects($this->any())
            ->method('addFieldToFilter')
            ->with(['is_searchable', 'is_visible_in_advanced_search', 'is_filterable'], [1, 1, [1, 2]])
            ->will($this->returnSelf());

        $this->productAttributeCollectionFactory->expects($this->any())
            ->method('create')
            ->willReturn($collection);
        $result = $this->object->generate();

        $this->assertEquals($countResult[0], $this->countVal($result['quick_search_container']['queries']));
        $this->assertEquals($countResult[1], $this->countVal($result['advanced_search_container']['queries']));
        $this->assertEquals($countResult[2], $this->countVal($result['advanced_search_container']['filters']));
    }

    /**
     * Create attribute mock
     *
     * @param string $code
     * @param string $type
     * @param bool $visibleInAdvanced
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function createAttributeMock($code, $type, $visibleInAdvanced = true)
    {
        $attribute = $this->getMockBuilder('Magento\Catalog\Model\Resource\Product\Attribute')
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'getAttributeCode',
                    'getBackendType',
                    'getIsVisibleInAdvancedSearch',
                    'getSearchWeight',
                    'getFrontendInput',
                    'getIsFilterable',
                    'getIsSearchable',
                ]
            )
            ->getMock();
        $attribute->expects($this->any())
            ->method('getAttributeCode')
            ->willReturn($code);
        $attribute->expects($this->any())
            ->method('getBackendType')
            ->willReturn($type);
        $attribute->expects($this->any())
            ->method('getFrontendInput')
            ->willReturn($type);

        $attribute->expects($this->any())
            ->method('getSearchWeight')
            ->willReturn(1);

        $attribute->expects($this->any())
            ->method('getIsVisibleInAdvancedSearch')
            ->willReturn($visibleInAdvanced);

        $attribute->expects($this->any())
            ->method('getIsFilterable')
            ->willReturn($visibleInAdvanced);

        $attribute->expects($this->any())
            ->method('getIsSearchable')
            ->willReturn(1);

        return $attribute;
    }

    private function countVal(&$value)
    {
        return !empty($value) ? count($value) : 0;
    }
}
