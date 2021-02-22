<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Test\Unit\Model\Product\TypeTransitionManager\Plugin;

class DownloadableTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $requestMock;

    /**
     * @var \Closure
     */
    protected $closureMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $productMock;

    /**
     * @var \Magento\Downloadable\Model\Product\TypeTransitionManager\Plugin\Downloadable
     */
    protected $model;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $weightResolver;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $subjectMock;

    protected function setUp(): void
    {
        $this->requestMock = $this->createMock(\Magento\Framework\App\Request\Http::class);
        $this->productMock = $this->createPartialMock(
            \Magento\Catalog\Model\Product::class,
            ['getTypeId', 'setTypeId']
        );
        $this->weightResolver = $this->createMock(\Magento\Catalog\Model\Product\Edit\WeightResolver::class);
        $this->subjectMock = $this->createMock(\Magento\Catalog\Model\Product\TypeTransitionManager::class);
        $this->closureMock = function () {
        };
        $this->model = new \Magento\Downloadable\Model\Product\TypeTransitionManager\Plugin\Downloadable(
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
            ->with(\Magento\Downloadable\Model\Product\Type::TYPE_DOWNLOADABLE);

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
            [\Magento\Downloadable\Model\Product\Type::TYPE_DOWNLOADABLE]
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
            [false, \Magento\Downloadable\Model\Product\Type::TYPE_DOWNLOADABLE, ['link' => [['is_delete' => '1']]]]
        ];
    }
}
