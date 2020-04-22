<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Downloadable\Test\Unit\Model\Product\TypeTransitionManager\Plugin;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Edit\WeightResolver;
use Magento\Catalog\Model\Product\TypeTransitionManager;
use Magento\Downloadable\Model\Product\Type;
use Magento\Downloadable\Model\Product\TypeTransitionManager\Plugin\Downloadable;
use Magento\Framework\App\Request\Http;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DownloadableTest extends TestCase
{
    /**
     * @var MockObject
     */
    protected $requestMock;

    /**
     * @var \Closure
     */
    protected $closureMock;

    /**
     * @var MockObject
     */
    protected $productMock;

    /**
     * @var Downloadable
     */
    protected $model;

    /**
     * @var MockObject
     */
    protected $weightResolver;

    /**
     * @var MockObject
     */
    protected $subjectMock;

    protected function setUp(): void
    {
        $this->requestMock = $this->createMock(Http::class);
        $this->productMock = $this->createPartialMock(
            Product::class,
            ['getTypeId', 'setTypeId']
        );
        $this->weightResolver = $this->createMock(WeightResolver::class);
        $this->subjectMock = $this->createMock(TypeTransitionManager::class);
        $this->closureMock = function () {
        };
        $this->model = new Downloadable(
            $this->requestMock,
            $this->weightResolver
        );
    }

    /**
     * @param string $currentTypeId
     * @dataProvider compatibleTypeDataProvider
     */
    public function testAroundProcessProductWithProductThatCanBeTransformedToDownloadable($currentTypeId)
    {
        $this->requestMock->expects($this->any())
            ->method('getPost')
            ->with('downloadable')
            ->willReturn(['link' => [['is_delete' => '']]]);
        $this->weightResolver->expects($this->any())->method('resolveProductHasWeight')->willReturn(false);
        $this->productMock->expects($this->once())->method('getTypeId')->willReturn($currentTypeId);
        $this->productMock->expects($this->once())
            ->method('setTypeId')
            ->with(Type::TYPE_DOWNLOADABLE);

        $this->model->aroundProcessProduct($this->subjectMock, $this->closureMock, $this->productMock);
    }

    /**
     * @return array
     */
    public function compatibleTypeDataProvider()
    {
        return [
            [\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE],
            [\Magento\Catalog\Model\Product\Type::TYPE_VIRTUAL],
            [Type::TYPE_DOWNLOADABLE]
        ];
    }

    /**
     * @param bool $hasWeight
     * @param string $currentTypeId
     * @param string|null $downloadableData
     * @dataProvider productThatCannotBeTransformedToDownloadableDataProvider
     */
    public function testAroundProcessProductWithProductThatCannotBeTransformedToDownloadable(
        $hasWeight,
        $currentTypeId,
        $downloadableData
    ) {
        $this->requestMock->expects($this->any())
            ->method('getPost')
            ->with('downloadable')
            ->willReturn($downloadableData);
        $this->weightResolver->expects($this->any())->method('resolveProductHasWeight')->willReturn($hasWeight);
        $this->productMock->expects($this->once())->method('getTypeId')->willReturn($currentTypeId);
        $this->productMock->expects($this->never())->method('setTypeId');

        $this->model->aroundProcessProduct($this->subjectMock, $this->closureMock, $this->productMock);
    }

    /**
     * @return array
     */
    public function productThatCannotBeTransformedToDownloadableDataProvider()
    {
        return [
            [false, 'custom_product_type', ['link' => [['is_delete' => '']]]],
            [true, \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE, null],
            [false, \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE, null],
            [true, \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE, ['link' => [['is_delete' => '']]]],
            [false, Type::TYPE_DOWNLOADABLE, ['link' => [['is_delete' => '1']]]]
        ];
    }
}
