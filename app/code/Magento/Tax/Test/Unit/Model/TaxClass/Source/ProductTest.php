<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Test\Unit\Model\TaxClass\Source;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ProductTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Tax\Api\TaxClassRepositoryInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $taxClassRepositoryMock;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $searchCriteriaBuilderMock;

    /**
     * @var \Magento\Framework\Api\FilterBuilder|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $filterBuilderMock;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var \Magento\Tax\Model\TaxClass\Source\Product
     */
    protected $product;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);

        $this->taxClassRepositoryMock = $this->getMockForAbstractClass(
            \Magento\Tax\Api\TaxClassRepositoryInterface::class,
            ['getList'],
            '',
            false,
            true,
            true,
            []
        );
        $this->searchCriteriaBuilderMock = $this->createPartialMock(
            \Magento\Framework\Api\SearchCriteriaBuilder::class,
            ['addFilters', 'create']
        );
        $this->filterBuilderMock = $this->createPartialMock(
            \Magento\Framework\Api\FilterBuilder::class,
            ['setField', 'setValue', 'create']
        );

        $this->product = $this->objectManager->getObject(
            \Magento\Tax\Model\TaxClass\Source\Product::class,
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
            \Magento\Eav\Model\Entity\Attribute\AbstractAttribute::class,
            ['getAttributeCode', '__wakeup']
        );

        $abstractAttrMock->expects($this->any())->method('getAttributeCode')->willReturn('code');

        $this->product->setAttribute($abstractAttrMock);

        $flatColumns = $this->product->getFlatColumns();

        $this->assertIsArray($flatColumns, 'FlatColumns must be an array value');
        $this->assertTrue(!empty($flatColumns), 'FlatColumns must be not empty');
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
        $filterMock = $this->createMock(\Magento\Framework\Api\Filter::class);
        $searchCriteriaMock = $this->createMock(\Magento\Framework\Api\SearchCriteria::class);
        $searchResultsMock = $this->getMockForAbstractClass(
            \Magento\Tax\Api\Data\TaxClassSearchResultsInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['getItems']
        );
        $taxClassMock = $this->getMockForAbstractClass(
            \Magento\Tax\Api\Data\TaxClassInterface::class,
            ['getClassId', 'getClassName'],
            '',
            false,
            true,
            true
        );

        $this->filterBuilderMock->expects($this->once())
            ->method('setField')
            ->with(\Magento\Tax\Model\ClassModel::KEY_TYPE)
            ->willReturnSelf();
        $this->filterBuilderMock->expects($this->once())
            ->method('setValue')
            ->with(\Magento\Tax\Api\TaxClassManagementInterface::TYPE_PRODUCT)
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
    public function dataProviderGetAllOptionsNameIntegrity()
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
