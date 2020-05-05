<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Test\Unit\Model\Product\TypeTransitionManager\Plugin;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\TypeTransitionManager;
use Magento\ConfigurableProduct\Model\Product\TypeTransitionManager\Plugin\Configurable;
use Magento\Framework\App\Request\Http;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ConfigurableTest extends TestCase
{
    /**
     * @var MockObject
     */
    protected $requestMock;

    /**
     * @var MockObject
     */
    protected $closureMock;

    /**
     * @var MockObject
     */
    protected $productMock;

    /**
     * @var Configurable
     */
    protected $model;

    /**
     * @var MockObject
     */
    protected $subjectMock;

    protected function setUp(): void
    {
        $this->requestMock = $this->createMock(Http::class);
        $this->model = new Configurable(
            $this->requestMock
        );
        $this->productMock = $this->createPartialMock(Product::class, ['setTypeId']);
        $this->subjectMock = $this->createMock(TypeTransitionManager::class);
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
