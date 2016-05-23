<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Test\Unit\Model\Product\TypeTransitionManager\Plugin;

class DownloadableTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    /**
     * @var \Closure
     */
    protected $closureMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productMock;

    /**
     * @var \Magento\Downloadable\Model\Product\TypeTransitionManager\Plugin\Downloadable
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $weightResolver;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $subjectMock;

    protected function setUp()
    {
        $this->requestMock = $this->getMock('Magento\Framework\App\Request\Http', [], [], '', false);
        $this->productMock = $this->getMock(
            'Magento\Catalog\Model\Product',
            ['getTypeId', 'setTypeId'],
            [],
            '',
            false
        );
        $this->weightResolver = $this->getMock('Magento\Catalog\Model\Product\Edit\WeightResolver');
        $this->subjectMock = $this->getMock('Magento\Catalog\Model\Product\TypeTransitionManager', [], [], '', false);
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
            ->will($this->returnValue(['link' => [['is_delete' => '']]]));
        $this->weightResolver->expects($this->any())->method('resolveProductHasWeight')->willReturn(false);
        $this->productMock->expects($this->once())->method('getTypeId')->will($this->returnValue($currentTypeId));
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
            ->will($this->returnValue($downloadableData));
        $this->weightResolver->expects($this->any())->method('resolveProductHasWeight')->willReturn($hasWeight);
        $this->productMock->expects($this->once())->method('getTypeId')->will($this->returnValue($currentTypeId));
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
