<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Tax\Test\Unit\Model\TaxClass\Source;

use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\Api\Filter;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Tax\Api\Data\TaxClassInterface;
use Magento\Tax\Api\Data\TaxClassSearchResultsInterface;
use Magento\Tax\Api\TaxClassManagementInterface;
use Magento\Tax\Api\TaxClassRepositoryInterface;
use Magento\Tax\Model\ClassModel;
use Magento\Tax\Model\TaxClass\Source\Product;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ProductTest extends TestCase
{
    /**
     * @var TaxClassRepositoryInterface|MockObject
     */
    protected $taxClassRepositoryMock;

    /**
     * @var SearchCriteriaBuilder|MockObject
     */
    protected $searchCriteriaBuilderMock;

    /**
     * @var FilterBuilder|MockObject
     */
    protected $filterBuilderMock;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var Product
     */
    protected $product;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);

        $this->taxClassRepositoryMock = $this->getMockForAbstractClass(
            TaxClassRepositoryInterface::class,
            ['getList'],
            '',
            false,
            true,
            true,
            []
        );
        $this->searchCriteriaBuilderMock = $this->createPartialMock(
            SearchCriteriaBuilder::class,
            ['addFilters', 'create']
        );
        $this->filterBuilderMock = $this->createPartialMock(
            FilterBuilder::class,
            ['setField', 'setValue', 'create']
        );

        $this->product = $this->objectManager->getObject(
            Product::class,
            [
                'taxClassRepository' => $this->taxClassRepositoryMock,
                'searchCriteriaBuilder' => $this->searchCriteriaBuilderMock,
                'filterBuilder' => $this->filterBuilderMock
            ]
        );
    }

    public function testGetFlatColumns()
    {
        $abstractAttrMock = $this->createPartialMock(
            AbstractAttribute::class,
            ['getAttributeCode', '__wakeup']
        );

        $abstractAttrMock->expects($this->any())->method('getAttributeCode')->willReturn('code');

        $this->product->setAttribute($abstractAttrMock);

        $flatColumns = $this->product->getFlatColumns();

        $this->assertIsArray($flatColumns, 'FlatColumns must be an array value');
        $this->assertNotEmpty($flatColumns, 'FlatColumns must be not empty');
        foreach ($flatColumns as $result) {
            $this->assertArrayHasKey('unsigned', $result, 'FlatColumns must have "unsigned" column');
            $this->assertArrayHasKey('default', $result, 'FlatColumns must have "default" column');
            $this->assertArrayHasKey('extra', $result, 'FlatColumns must have "extra" column');
            $this->assertArrayHasKey('type', $result, 'FlatColumns must have "type" column');
            $this->assertArrayHasKey('nullable', $result, 'FlatColumns must have "nullable" column');
            $this->assertArrayHasKey('comment', $result, 'FlatColumns must have "comment" column');
        }
    }

    /**
     * Run test getAllOptions method for names integrity
     *
     * @param array $value
     * @dataProvider dataProviderGetAllOptionsNameIntegrity
     */
    public function testGetAllOptionsNameIntegrity(array $value)
    {
        $filterMock = $this->createMock(Filter::class);
        $searchCriteriaMock = $this->createMock(SearchCriteria::class);
        $searchResultsMock = $this->getMockForAbstractClass(
            TaxClassSearchResultsInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['getItems']
        );
        $taxClassMock = $this->getMockForAbstractClass(
            TaxClassInterface::class,
            ['getClassId', 'getClassName'],
            '',
            false,
            true,
            true
        );

        $this->filterBuilderMock->expects($this->once())
            ->method('setField')
            ->with(ClassModel::KEY_TYPE)
            ->willReturnSelf();
        $this->filterBuilderMock->expects($this->once())
            ->method('setValue')
            ->with(TaxClassManagementInterface::TYPE_PRODUCT)
            ->willReturnSelf();
        $this->filterBuilderMock->expects($this->once())
            ->method('create')
            ->willReturn($filterMock);
        $this->searchCriteriaBuilderMock->expects($this->once())
            ->method('addFilters')
            ->with([$filterMock])
            ->willReturnSelf();
        $this->searchCriteriaBuilderMock->expects($this->once())
            ->method('create')
            ->willReturn($searchCriteriaMock);
        $this->taxClassRepositoryMock->expects($this->once())
            ->method('getList')
            ->with($searchCriteriaMock)
            ->willReturn($searchResultsMock);

        $taxClassMock->expects($this->once())
            ->method('getClassId')
            ->willReturn($value['value']);
        $taxClassMock->expects($this->once())
            ->method('getClassName')
            ->willReturn($value['label']);

        $items = [$taxClassMock];
        $searchResultsMock->expects($this->once())
            ->method('getItems')
            ->willReturn($items);

        $result=($this->product->getAllOptions(false));
        $expected=$value;
        $this->assertEquals([$expected], $result);
    }

    /**
     * Data provider for testGetAllOptionsNameIntegrity
     *
     * @return array
     */
    public static function dataProviderGetAllOptionsNameIntegrity()
    {
        return [
            [
                'value' => ['value' => 1, 'label' => 'unescaped name'],
            ],
            [
                'value' => ['value' => 2, 'label' => 'tax < 50%'],
            ],
            [
                'value' => ['value' => 3, 'label' => 'rule for 1 & 2'],
            ],
            [
                'value' => ['value' => 4, 'label' => 'html <tag>'],
            ],
            [
                'value' => ['value' => 5, 'label' => 'comment <!-- comment -->'],
            ],
            [
                'value' => ['value' => 6, 'label' => 'php tag <?= "2"; ?>'],
            ],

        ];
    }
}
