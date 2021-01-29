<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Test\Unit\Model\Product\CartConfiguration\Plugin;

class ConfigurableTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\ConfigurableProduct\Model\Product\CartConfiguration\Plugin\Configurable
     */
    protected $model;

    /**
     * @var \Closure
     */
    protected $closureMock;

    /**
     * @var \PHPUnit_invFramework_MockObject_MockObject
     */
    protected $productMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $subjectMock;

    protected function setUp(): void
    {
        $this->closureMock = function () {
            return 'Expected';
        };
        $this->productMock = $this->createMock(\Magento\Catalog\Model\Product::class);
        $this->subjectMock = $this->createMock(\Magento\Catalog\Model\Product\CartConfiguration::class);
        $this->model = new \Magento\ConfigurableProduct\Model\Product\CartConfiguration\Plugin\Configurable();
    }

    public function testAroundIsProductConfiguredChecksThatSuperAttributeIsSetWhenProductIsConfigurable()
    {
        $config = ['super_attribute' => 'valid_value'];
        $this->productMock->expects(
            $this->once()
        )->method(
            'getTypeId'
        )->willReturn(
            \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE
        );
        $this->assertTrue(
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
        )->willReturn(
            'custom_product_type'
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
