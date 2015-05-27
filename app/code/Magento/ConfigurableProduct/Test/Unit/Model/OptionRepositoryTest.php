<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Test\Unit\Model;

class OptionRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\ConfigurableProduct\Model\OptionRepository
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productRepositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productMock;

    protected function setUp()
    {
        $this->productRepositoryMock = $this->getMock('\Magento\Catalog\Api\ProductRepositoryInterface');
        $this->productMock = $this->getMock('\Magento\Catalog\Api\Data\ProductInterface');
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $objectManager->getObject(
            '\Magento\ConfigurableProduct\Model\OptionRepository',
            [
                'productRepository' => $this->productRepositoryMock,
            ]
        );
    }

    public function testGet()
    {
        $productSku = "configurable";
        $optionId = 3;

        $this->productRepositoryMock->expects($this->once())
            ->method('get')
            ->with($productSku)
            ->willReturn($this->productMock);
        $this->productMock->expects($this->once())
            ->method('getTypeId')
            ->willReturn(\Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE);

        $optionMock = $this->getMock('\Magento\ConfigurableProduct\Api\Data\OptionInterface');
        $optionMock->expects($this->once())
            ->method('getId')
            ->willReturn($optionId);
        $productExtensionMock = $this->getMockBuilder('\Magento\Catalog\Api\Data\ProductExtension')
            ->setMethods(['getConfigurableProductOptions'])
            ->getMock();
        $productExtensionMock->expects($this->once())
            ->method('getConfigurableProductOptions')
            ->willReturn([$optionMock]);
        $this->productMock->expects($this->once())
            ->method('getExtensionAttributes')
            ->willReturn($productExtensionMock);

        $this->assertEquals($optionMock, $this->model->get($productSku, $optionId));
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage Only implemented for configurable product: configurable
     */
    public function testGetNotConfigurableProduct()
    {
        $productSku = "configurable";
        $optionId = 3;

        $this->productRepositoryMock->expects($this->once())
            ->method('get')
            ->with($productSku)
            ->willReturn($this->productMock);
        $this->productMock->expects($this->once())
            ->method('getTypeId')
            ->willReturn('simple');

        $this->model->get($productSku, $optionId);
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage Requested option doesn't exist: 3
     */
    public function testGetEmptyExtensionAttribute()
    {
        $productSku = "configurable";
        $optionId = 3;

        $this->productRepositoryMock->expects($this->once())
            ->method('get')
            ->with($productSku)
            ->willReturn($this->productMock);
        $this->productMock->expects($this->once())
            ->method('getTypeId')
            ->willReturn(\Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE);

        $this->productMock->expects($this->once())
            ->method('getExtensionAttributes')
            ->willReturn(null);

        $this->model->get($productSku, $optionId);
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage Requested option doesn't exist: 3
     */
    public function testGetOptionIdNotFound()
    {
        $productSku = "configurable";
        $optionId = 3;

        $this->productRepositoryMock->expects($this->once())
            ->method('get')
            ->with($productSku)
            ->willReturn($this->productMock);
        $this->productMock->expects($this->once())
            ->method('getTypeId')
            ->willReturn(\Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE);

        $optionMock = $this->getMock('\Magento\ConfigurableProduct\Api\Data\OptionInterface');
        $optionMock->expects($this->once())
            ->method('getId')
            ->willReturn(6);
        $productExtensionMock = $this->getMockBuilder('\Magento\Catalog\Api\Data\ProductExtension')
            ->setMethods(['getConfigurableProductOptions'])
            ->getMock();
        $productExtensionMock->expects($this->once())
            ->method('getConfigurableProductOptions')
            ->willReturn([$optionMock]);
        $this->productMock->expects($this->once())
            ->method('getExtensionAttributes')
            ->willReturn($productExtensionMock);

        $this->model->get($productSku, $optionId);
    }

    public function testGetList()
    {
        $productSku = "configurable";

        $this->productRepositoryMock->expects($this->once())
            ->method('get')
            ->with($productSku)
            ->willReturn($this->productMock);
        $this->productMock->expects($this->once())
            ->method('getTypeId')
            ->willReturn(\Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE);

        $optionMock = $this->getMock('\Magento\ConfigurableProduct\Api\Data\OptionInterface');
        $productExtensionMock = $this->getMockBuilder('\Magento\Catalog\Api\Data\ProductExtension')
            ->setMethods(['getConfigurableProductOptions'])
            ->getMock();
        $productExtensionMock->expects($this->once())
            ->method('getConfigurableProductOptions')
            ->willReturn([$optionMock]);
        $this->productMock->expects($this->once())
            ->method('getExtensionAttributes')
            ->willReturn($productExtensionMock);

        $this->assertEquals([$optionMock], $this->model->getList($productSku));
    }

    public function testGetListNullExtensionAttribute()
    {
        $productSku = "configurable";

        $this->productRepositoryMock->expects($this->once())
            ->method('get')
            ->with($productSku)
            ->willReturn($this->productMock);
        $this->productMock->expects($this->once())
            ->method('getTypeId')
            ->willReturn(\Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE);

        $this->productMock->expects($this->once())
            ->method('getExtensionAttributes')
            ->willReturn(null);

        $this->assertEquals([], $this->model->getList($productSku));
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage Only implemented for configurable product: configurable
     */
    public function testGetListNotConfigurableProduct()
    {
        $productSku = "configurable";

        $this->productRepositoryMock->expects($this->once())
            ->method('get')
            ->with($productSku)
            ->willReturn($this->productMock);
        $this->productMock->expects($this->once())
            ->method('getTypeId')
            ->willReturn('simple');

        $this->model->getList($productSku);
    }

    /**
     * @param int $attributeId
     * @param string $label
     * @param array $optionValues
     * @param string $msg
     * @dataProvider validateOptionDataProvider
     * @throws \Magento\Framework\Exception\InputException
     */
    public function testValidateNewOptionData($attributeId, $label, $optionValues, $msg)
    {
        $this->setExpectedException('Magento\Framework\Exception\InputException', $msg);
        $optionValueMock = $this->getMock('\Magento\ConfigurableProduct\Api\Data\OptionValueInterface');
        $optionValuesMock = [];
        if (!empty($optionValues)) {
            $optionValueMock->expects($this->any())
                ->method('getValueIndex')
                ->willReturn($optionValues['v']);
            $optionValueMock->expects($this->any())
                ->method('getPricingValue')
                ->willReturn($optionValues['p']);
            $optionValueMock->expects($this->any())
                ->method('getIsPercent')
                ->willReturn($optionValues['r']);
            $optionValuesMock = [$optionValueMock];
        }

        $optionMock = $this->getMock('\Magento\ConfigurableProduct\Api\Data\OptionInterface');
        $optionMock->expects($this->any())
            ->method('getAttributeId')
            ->willReturn($attributeId);
        $optionMock->expects($this->any())
            ->method('getLabel')
            ->willReturn($label);
        $optionMock->expects($this->any())
            ->method('getValues')
            ->willReturn($optionValuesMock);

        $this->model->validateNewOptionData($optionMock);
    }

    public function validateOptionDataProvider()
    {
        return [
            [null, '', ['v' => null, 'p' => null, 'r' => null], 'One or more input exceptions have occurred.'],
            [1, 'Label', [], 'Option values are not specified.'],
            [null, 'Label', ['v' => 1, 'p' => 1, 'r' => 1], 'Option attribute ID is not specified.'],
            [1, '', ['v' => 1, 'p' => 1, 'r' => 1], 'Option label is not specified.'],
            [1, 'Label', ['v' => null, 'p' => 1, 'r' => 1], 'Value index is not specified for an option.'],
            [1, 'Label', ['v' => 1, 'p' => null, 'r' => 1], 'Price is not specified for an option.'],
            [1, 'Label', ['v' => 1, 'p' => 1, 'r' => null], 'Percent/absolute is not specified for an option.'],
        ];
    }
}
