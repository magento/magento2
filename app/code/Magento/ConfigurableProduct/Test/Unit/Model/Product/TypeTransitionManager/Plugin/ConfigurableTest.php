<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Test\Unit\Model\Product\TypeTransitionManager\Plugin;

class ConfigurableTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $requestMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $closureMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $productMock;

    /**
     * @var \Magento\ConfigurableProduct\Model\Product\TypeTransitionManager\Plugin\Configurable
     */
    protected $model;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $subjectMock;

    protected function setUp(): void
    {
        $this->requestMock = $this->createMock(\Magento\Framework\App\Request\Http::class);
        $this->model = new \Magento\ConfigurableProduct\Model\Product\TypeTransitionManager\Plugin\Configurable(
            $this->requestMock
        );
        $this->productMock = $this->createPartialMock(\Magento\Catalog\Model\Product::class, ['setTypeId', '__wakeup']);
        $this->subjectMock = $this->createMock(\Magento\Catalog\Model\Product\TypeTransitionManager::class);
        $this->closureMock = function () {
            return 'Expected';
        };
    }

    public function testAroundProcessProductWithProductThatCanBeTransformedToConfigurable()
    {
        $this->requestMock->expects(
            $this->any()
        )->method(
            'getParam'
        )->with(
            'attributes'
        )->willReturn(
            'not_empty_attribute_data'
        );
        $this->productMock->expects(
            $this->once()
        )->method(
            'setTypeId'
        )->with(
            \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE
        );
        $this->model->aroundProcessProduct($this->subjectMock, $this->closureMock, $this->productMock);
    }

    public function testAroundProcessProductWithProductThatCannotBeTransformedToConfigurable()
    {
        $this->requestMock->expects(
            $this->any()
        )->method(
            'getParam'
        )->with(
            'attributes'
        )->willReturn(
            null
        );
        $this->productMock->expects($this->never())->method('setTypeId');
        $this->model->aroundProcessProduct($this->subjectMock, $this->closureMock, $this->productMock);
    }
}
