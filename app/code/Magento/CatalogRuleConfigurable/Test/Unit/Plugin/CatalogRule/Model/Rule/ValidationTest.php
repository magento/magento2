<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogRuleConfigurable\Test\Unit\Plugin\CatalogRule\Model\Rule;

use Magento\CatalogRule\Model\Rule;
use Magento\CatalogRuleConfigurable\Plugin\CatalogRule\Model\Rule\Validation;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\DataObject;
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

    /** @var Rule|MockObject */
    private $ruleMock;

    /** @var Combine|MockObject */
    private $ruleConditionsMock;

    /** @var DataObject|MockObject */
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

        $this->ruleMock = $this->createMock(Rule::class);
        $this->ruleConditionsMock = $this->createMock(Combine::class);
        $this->productMock = $this->getMockBuilder(DataObject::class)
            ->addMethods(['getId'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->validation = new Validation(
            $this->configurableMock
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
        $this->productMock->expects($this->once())->method('getId')->willReturn('product_id');
        $this->configurableMock->expects($this->once())->method('getParentIdsByChild')->with('product_id')
            ->willReturn($parentsIds);
        $this->ruleMock->expects($this->exactly($runValidateAmount))->method('getConditions')
            ->willReturn($this->ruleConditionsMock);
        $this->ruleConditionsMock->expects($this->exactly($runValidateAmount))->method('validateByEntityId')
            ->willReturnMap($validationResult);

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
                [
                    [1, false],
                    [2, true],
                    [3, true],
                ],
                2,
                true,
            ],
            [
                [1, 2, 3],
                [
                    [1, true],
                    [2, false],
                    [3, true],
                ],
                1,
                true,
            ],
            [
                [1, 2, 3],
                [
                    [1, false],
                    [2, false],
                    [3, false],
                ],
                3,
                false,
            ],
        ];
    }
}
