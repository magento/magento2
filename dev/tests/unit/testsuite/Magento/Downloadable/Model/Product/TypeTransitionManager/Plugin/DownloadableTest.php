<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Model\Product\TypeTransitionManager\Plugin;

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
    protected $subjectMock;

    protected function setUp()
    {
        $this->requestMock = $this->getMock('Magento\Framework\App\Request\Http', [], [], '', false);
        $this->model = new Downloadable($this->requestMock);
        $this->productMock = $this->getMock(
            'Magento\Catalog\Model\Product',
            ['hasIsVirtual', 'getTypeId', 'setTypeId', '__wakeup'],
            [],
            '',
            false
        );
        $this->subjectMock = $this->getMock('Magento\Catalog\Model\Product\TypeTransitionManager', [], [], '', false);
        $this->closureMock = function () {
        };
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
        $this->productMock->expects($this->any())->method('hasIsVirtual')->will($this->returnValue(true));
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
     * @param bool $isVirtual
     * @param string $currentTypeId
     * @param string|null $downloadableData
     * @dataProvider productThatCannotBeTransformedToDownloadableDataProvider
     */
    public function testAroundProcessProductWithProductThatCannotBeTransformedToDownloadable(
        $isVirtual,
        $currentTypeId,
        $downloadableData
    ) {
        $this->requestMock->expects($this->any())
            ->method('getPost')
            ->with('downloadable')
            ->will($this->returnValue($downloadableData));
        $this->productMock->expects($this->any())->method('hasIsVirtual')->will($this->returnValue($isVirtual));
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
            [true, 'custom_product_type', ['link' => [['is_delete' => '']]]],
            [false, \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE, null],
            [true, \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE, null],
            [false, \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE, ['link' => [['is_delete' => '']]]],
            [true, \Magento\Downloadable\Model\Product\Type::TYPE_DOWNLOADABLE, ['link' => [['is_delete' => '1']]]]
        ];
    }
}
