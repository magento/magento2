<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Test\Unit\Model\TaxClass\Source;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class CustomerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Tax\Api\TaxClassRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $taxClassRepositoryMock;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $searchCriteriaBuilderMock;

    /**
     * @var \Magento\Framework\Api\FilterBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $filterBuilderMock;

    /**
     * @var \Magento\Tax\Model\TaxClass\Source\Customer
     */
    protected $customer;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * Set up
     *
     * @return void
     */
    protected function setUp()
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
        $this->searchCriteriaBuilderMock = $this->getMock(
            \Magento\Framework\Api\SearchCriteriaBuilder::class,
            ['addFilters', 'create'],
            [],
            '',
            false
        );
        $this->filterBuilderMock = $this->getMock(
            \Magento\Framework\Api\FilterBuilder::class,
            ['setField', 'setValue', 'create'],
            [],
            '',
            false
        );

        $this->customer = $this->objectManager->getObject(
            \Magento\Tax\Model\TaxClass\Source\Customer::class,
            [
                'taxClassRepository' => $this->taxClassRepositoryMock,
                'searchCriteriaBuilder' => $this->searchCriteriaBuilderMock,
                'filterBuilder' => $this->filterBuilderMock
            ]
        );
    }

    /**
     * Run test getAllOptions method
     *
     * @param bool $isEmpty
     * @param array $expected
     * @dataProvider dataProviderGetAllOptions
     */
    public function testGetAllOptions($isEmpty, array $expected)
    {
        $filterMock = $this->getMock(
            \Magento\Framework\Api\Filter::class,
            [],
            [],
            '',
            false
        );
        $searchCriteriaMock = $this->getMock(
            \Magento\Framework\Api\SearchCriteria::class,
            [],
            [],
            '',
            false
        );
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
            ->with(\Magento\Tax\Api\TaxClassManagementInterface::TYPE_CUSTOMER)
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

        if (!$isEmpty) {
            $taxClassMock->expects($this->once())
                ->method('getClassId')
                ->willReturn(10);
            $taxClassMock->expects($this->once())
                ->method('getClassName')
                ->willReturn('class-name');

            $items = [$taxClassMock];
            $searchResultsMock->expects($this->once())
                ->method('getItems')
                ->willReturn($items);

            // checking of a lack of re-initialization
            for ($i = 10; --$i;) {
                $result = $this->customer->getAllOptions();
                $this->assertEquals($expected, $result);
            }
        } else {
            $items = [];
            $searchResultsMock->expects($this->once())
                ->method('getItems')
                ->willReturn($items);
            // checking exception
            $this->assertEmpty($this->customer->getAllOptions());
        }
    }

    /**
     * Data provider for testGetAllOptions
     *
     * @return array
     */
    public function dataProviderGetAllOptions()
    {
        return [
            ['isEmpty' => false, 'expected' => [['value' => 10, 'label' => 'class-name']]],
            ['isEmpty' => true, 'expected' => []]
        ];
    }

    /**
     * Run test getAllOptions method for names integrity
     *
     * @param array $value
     * @dataProvider dataProviderGetAllOptionsNameIntegrity
     */
    public function testGetAllOptionsNameIntegrity(array $value)
    {
        $filterMock = $this->getMock(
            \Magento\Framework\Api\Filter::class,
            [],
            [],
            '',
            false
        );
        $searchCriteriaMock = $this->getMock(
            \Magento\Framework\Api\SearchCriteria::class,
            [],
            [],
            '',
            false
        );
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
            ->with(\Magento\Tax\Api\TaxClassManagementInterface::TYPE_CUSTOMER)
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

        $result=($this->customer->getAllOptions());
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
                'value' => ['value' => 6, 'label' => 'php tag <?php echo "2"; ?>'],
            ],

        ];
    }
}
