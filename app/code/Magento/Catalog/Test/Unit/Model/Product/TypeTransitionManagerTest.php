<?php
/**
 * Copyright 2024 Adobe
 * All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Product;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Edit\WeightResolver;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\Product\TypeTransitionManager;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class TypeTransitionManagerTest extends TestCase
{
    /**
     * @var TypeTransitionManager
     */
    protected $model;

    /**
     * @var MockObject
     */
    protected $productMock;

    /**
     * @var MockObject
     */
    protected $weightResolver;

    protected function setUp(): void
    {
        $this->productMock = $this->createPartialMock(
            Product::class,
            ['getTypeId', 'setTypeId', 'setTypeInstance']
        );
        $this->weightResolver = $this->createMock(WeightResolver::class);
        $this->model = (new ObjectManager($this))
            ->getObject(
                TypeTransitionManager::class,
                [
                    'weightResolver' => $this->weightResolver,
                    'compatibleTypes' => [
                        'simple' => Type::TYPE_SIMPLE,
                        'virtual' => Type::TYPE_VIRTUAL,
                    ]
                ]
            );
    }

    /**
     * @param bool $hasWeight
     * @param string $currentTypeId
     * @param string $expectedTypeId
     * @dataProvider processProductDataProvider
     */
    public function testProcessProduct($hasWeight, $currentTypeId, $expectedTypeId)
    {
        $this->productMock->expects($this->once())->method('getTypeId')->willReturn($currentTypeId);
        $this->productMock->expects($this->once())->method('setTypeInstance')->with(null);
        $this->weightResolver->expects($this->once())->method('resolveProductHasWeight')->willReturn($hasWeight);
        $this->productMock->expects($this->once())->method('setTypeId')->with($expectedTypeId);
        $this->model->processProduct($this->productMock);
    }

    /**
     * @return void
     */
    public function testProcessProductWithWrongTypeId()
    {
        $this->productMock->expects($this->once())->method('getTypeId')->willReturn('wrong-type');
        $this->weightResolver->expects($this->never())->method('resolveProductHasWeight');
        $this->model->processProduct($this->productMock);
    }

    /**
     * @return array
     */
    public static function processProductDataProvider()
    {
        return [
            [
                true,
                Type::TYPE_VIRTUAL,
                Type::TYPE_SIMPLE,
            ],
            [
                true,
                Type::TYPE_SIMPLE,
                Type::TYPE_SIMPLE,
            ],
            [
                false,
                Type::TYPE_SIMPLE,
                Type::TYPE_VIRTUAL,
            ],
            [
                false,
                Type::TYPE_VIRTUAL,
                Type::TYPE_VIRTUAL,
            ]
        ];
    }
}
