<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogRuleConfigurable\Test\Unit\Plugin\CatalogRule\Model\Rule;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\CatalogRule\Model\Rule;
use Magento\CatalogRuleConfigurable\Plugin\CatalogRule\Model\Rule\Validation;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Rule\Model\Condition\Combine;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for Magento\CatalogRuleConfigurable\Plugin\CatalogRule\Model\Rule\Validation
 */
class ValidationTest extends TestCase
{
    /**
     * @var Validation
     */
    private $validation;

    /**
     * @var Configurable|MockObject
     */
    private $configurableMock;

    /**
     * @var ProductRepositoryInterface|MockObject
     */
    private $productRepositoryMock;

    /** @var Rule|MockObject */
    private $ruleMock;

    /** @var Combine|MockObject */
    private $ruleConditionsMock;

    /** @var Product|MockObject */
    private $productMock;

    /**
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        $this->configurableMock = $this->createPartialMock(
            Configurable::class,
            ['getParentIdsByChild']
        );
        $this->productRepositoryMock = $this->createMock(ProductRepositoryInterface::class);

        $this->ruleMock = $this->createMock(Rule::class);
        $this->ruleConditionsMock = $this->createMock(Combine::class);
        $this->productMock = $this->createMock(Product::class);

        $this->validation = new Validation(
            $this->configurableMock,
            $this->productRepositoryMock
        );
    }

    /**
     * @return void
     */
    public function testAfterValidateConfigurableProductException(): void
    {
        $validationResult = false;
        $parentsIds = [2];
        $productId = 1;

        $this->productMock->expects($this->once())
            ->method('getId')
            ->willReturn($productId);
        $this->configurableMock->expects($this->once())
            ->method('getParentIdsByChild')
            ->with($productId)
            ->willReturn($parentsIds);
        $this->productRepositoryMock->expects($this->once())
            ->method('getById')
            ->willThrowException(new \Exception('Faulty configurable product'));

        $this->assertSame(
            $validationResult,
            $this->validation->afterValidate($this->ruleMock, $validationResult, $this->productMock)
        );
    }

    /**
     * @param $parentsIds
     * @param $validationResult
     * @param $runValidateAmount
     * @param $result
     * @dataProvider dataProviderForValidateWithValidConfigurableProduct
     * @return void
     */
    public function testAfterValidateWithValidConfigurableProduct(
        $parentsIds,
        $validationResult,
        $runValidateAmount,
        $result
    ) {
        $storeId = 1;
        $this->productMock->expects($this->once())
            ->method('getId')
            ->willReturn(10);
        $this->configurableMock->expects($this->once())
            ->method('getParentIdsByChild')
            ->with(10)
            ->willReturn($parentsIds);
        $this->productMock->expects($this->exactly($runValidateAmount))
            ->method('getStoreId')
            ->willReturn($storeId);
        $parentsProducts = array_map(
            function ($parentsId) {
                $parent = $this->createMock(Product::class);
                $parent->method('getId')->willReturn($parentsId);
                return $parent;
            },
            $parentsIds
        );
        $this->productRepositoryMock->expects($this->exactly($runValidateAmount))
            ->method('getById')
            ->withConsecutive(
                ...array_map(
                    function ($parentsId) use ($storeId) {
                        return [$parentsId, false, $storeId];
                    },
                    $parentsIds
                )
            )->willReturnOnConsecutiveCalls(...$parentsProducts);
        $this->ruleMock->expects($this->exactly($runValidateAmount))
            ->method('getConditions')
            ->willReturn($this->ruleConditionsMock);
        $this->ruleConditionsMock->expects($this->exactly($runValidateAmount))
            ->method('validate')
            ->withConsecutive(
                ...array_map(
                    function ($parentsProduct) {
                        return [$parentsProduct];
                    },
                    $parentsProducts
                )
            )
            ->willReturnOnConsecutiveCalls(...$validationResult);

        $this->assertEquals(
            $result,
            $this->validation->afterValidate($this->ruleMock, false, $this->productMock)
        );
    }

    /**
     * @return array
     */
    public function dataProviderForValidateWithValidConfigurableProduct()
    {
        return [
            [
                [1, 2, 3],
                [false, true, true],
                2,
                true,
            ],
            [
                [1, 2, 3],
                [true, false, true],
                1,
                true,
            ],
            [
                [1, 2, 3],
                [false, false, false],
                3,
                false,
            ],
        ];
    }
}
