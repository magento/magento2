<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Unit\Model\Entity;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Api\Data\ProductAttributeSearchResultsInterface;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Model\Entity\GetProductCustomAttributeCodes;
use Magento\Eav\Model\Entity\GetCustomAttributeCodesInterface;
use Magento\Framework\Api\MetadataServiceInterface;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * Provide tests for GetProductCustomAttributeCodes entity model.
 */
class GetProductCustomAttributeCodesTest extends TestCase
{
    /**
     * Test subject.
     *
     * @var GetProductCustomAttributeCodes
     */
    private $getProductCustomAttributeCodes;

    /**
     * @var GetCustomAttributeCodesInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $baseCustomAttributeCodes;

    /**
     * @var SearchCriteriaBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $searchCriteriaBuilder;

    /**
     * @var ProductAttributeRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $attributeRepository;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->baseCustomAttributeCodes = $this->getMockBuilder(GetCustomAttributeCodesInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['execute'])
            ->getMockForAbstractClass();

        $this->searchCriteriaBuilder = $this->getMockBuilder(SearchCriteriaBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->attributeRepository = $this->getMockBuilder(ProductAttributeRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getList'])
            ->getMockForAbstractClass();

        $objectManager = new ObjectManager($this);
        $this->getProductCustomAttributeCodes = $objectManager->getObject(
            GetProductCustomAttributeCodes::class,
            [
                'baseCustomAttributeCodes' => $this->baseCustomAttributeCodes,
                'searchCriteriaBuilder' => $this->searchCriteriaBuilder,
                'attributeRepository' => $this->attributeRepository
            ]
        );
    }

    /**
     * Test GetProductCustomAttributeCodes::execute() will return only custom product attribute codes.
     */
    public function testExecute()
    {
        $metadataService = $this->getMockMetadataService();

        $this->baseCustomAttributeCodes->expects($this->once())
            ->method('execute')
            ->with($this->identicalTo($metadataService))
            ->willReturn(['test_custom_attribute_code', 'name']);

        $this->assertEquals(
            ['test_custom_attribute_code'],
            $this->getProductCustomAttributeCodes->execute($metadataService)
        );
    }

    public function testExecuteForAttributesInASet()
    {
        $metadataService = $this->getMockMetadataService();

        $this->baseCustomAttributeCodes->expects($this->never())->method('execute');

        $this->searchCriteriaBuilder->expects($this->once())
            ->method('addFilter')
            ->with('attribute_set_id', 99, 'eq')
            ->willReturnSelf();

        $this->searchCriteriaBuilder->expects($this->once())
            ->method('create')
            ->willReturn($this->getMockSearchCriteria());

        $result = $this->getMockBuilder(ProductAttributeSearchResultsInterface::class)
            ->setMethods(['getItems'])
            ->getMockForAbstractClass();

        $this->attributeRepository->expects($this->once())
            ->method('getList')
            ->willReturn($result);

        $result->expects($this->once())
            ->method('getItems')
            ->willReturn(
                [
                    $this->getMockAttribute('test_code'),
                    $this->getMockAttribute('another_code'),
                    $this->getMockAttribute('sku'),
                    $this->getMockAttribute('price')
                ]
            );

        $this->assertEquals(
            ['test_code', 'another_code'],
            $this->getProductCustomAttributeCodes->execute($metadataService, 99)
        );
    }

    public function testExecuteForAttributesInASetMemoizesResult()
    {
        $metadataService = $this->getMockMetadataService();

        $this->baseCustomAttributeCodes->expects($this->never())->method('execute');

        $this->searchCriteriaBuilder->expects($this->once())
            ->method('addFilter')
            ->with('attribute_set_id', 100, 'eq')
            ->willReturnSelf();

        $this->searchCriteriaBuilder->expects($this->once())
            ->method('create')
            ->willReturn($this->getMockSearchCriteria());

        $result = $this->getMockBuilder(ProductAttributeSearchResultsInterface::class)
            ->setMethods(['getItems'])
            ->getMockForAbstractClass();

        $this->attributeRepository->expects($this->once())
            ->method('getList')
            ->willReturn($result);

        $result->expects($this->once())
            ->method('getItems')
            ->willReturn([$this->getMockAttribute('foo'), $this->getMockAttribute('price')]);

        $this->assertEquals(
            ['foo'],
            $this->getProductCustomAttributeCodes->execute($metadataService, 100)
        );

        $this->assertEquals(
            ['foo'],
            $this->getProductCustomAttributeCodes->execute($metadataService, 100)
        );
    }

    public function testExecuteForAttributesInASetReturnsEmptyArrayWhenNoAttributesFound()
    {
        $metadataService = $this->getMockMetadataService();

        $this->baseCustomAttributeCodes->expects($this->never())->method('execute');

        $this->searchCriteriaBuilder->expects($this->once())
            ->method('addFilter')
            ->with('attribute_set_id', 101, 'eq')
            ->willReturnSelf();

        $this->searchCriteriaBuilder->expects($this->once())
            ->method('create')
            ->willReturn($this->getMockSearchCriteria());

        $result = $this->getMockBuilder(ProductAttributeSearchResultsInterface::class)
            ->setMethods(['getItems'])
            ->getMockForAbstractClass();

        $this->attributeRepository->expects($this->once())
            ->method('getList')
            ->willReturn($result);

        $result->expects($this->once())
            ->method('getItems')
            ->willReturn([]);

        $this->assertEquals(
            [],
            $this->getProductCustomAttributeCodes->execute($metadataService, 101)
        );
    }

    /**
     * @return MetadataServiceInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getMockMetadataService()
    {
        return $this->getMockBuilder(MetadataServiceInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
    }

    private function getMockSearchCriteria()
    {
        return $this->getMockBuilder(SearchCriteria::class)->disableOriginalConstructor()->getMock();
    }

    private function getMockAttribute($code)
    {
        $attribute = $this->getMockBuilder(ProductAttributeInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAttributeCode'])
            ->getMockForAbstractClass();

        $attribute->expects($this->any())
            ->method('getAttributeCode')
            ->willReturn($code);

        return $attribute;
    }
}
