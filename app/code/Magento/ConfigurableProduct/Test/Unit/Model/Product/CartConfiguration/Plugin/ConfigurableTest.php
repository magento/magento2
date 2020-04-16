<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Test\Unit\Model\Product\CartConfiguration\Plugin;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\CartConfiguration;
use Magento\ConfigurableProduct\Model\Product\CartConfiguration\Plugin\Configurable;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ConfigurableTest extends TestCase
{
    /**
     * @var Configurable
     */
    protected $model;

    /**
     * @var \Closure
     */
    protected $closureMock;

    /**
     * @var MockObject
     */
    protected $productMock;

    /**
     * @var MockObject
     */
    protected $subjectMock;

    protected function setUp(): void
    {
        $this->closureMock = function () {
            return 'Expected';
        };
        $this->productMock = $this->createMock(Product::class);
        $this->subjectMock = $this->createMock(CartConfiguration::class);
        $this->model = new Configurable();
    }

    public function testAroundIsProductConfiguredChecksThatSuperAttributeIsSetWhenProductIsConfigurable()
    {
        $config = ['super_attribute' => 'valid_value'];
        $this->productMock->expects(
            $this->once()
        )->method(
            'getTypeId'
        )->will(
            $this->returnValue(\Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE)
        );
        $this->assertEquals(
            true,
            $this->model->aroundIsProductConfigured(
                $this->subjectMock,
                $this->closureMock,
                $this->productMock,
                $config
            )
        );
    }

    public function testAroundIsProductConfiguredWhenProductIsNotConfigurable()
    {
        $config = ['super_group' => 'valid_value'];
        $this->productMock->expects(
            $this->once()
        )->method(
            'getTypeId'
        )->will(
            $this->returnValue('custom_product_type')
        );
        $this->assertEquals(
            'Expected',
            $this->model->aroundIsProductConfigured(
                $this->subjectMock,
                $this->closureMock,
                $this->productMock,
                $config
            )
        );
    }
}
