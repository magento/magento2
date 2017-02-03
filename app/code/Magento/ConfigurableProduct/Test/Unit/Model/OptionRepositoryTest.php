<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Test\Unit\Model;

use Magento\ConfigurableProduct\Model\Product\Type\Configurable;

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

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $configurableTypeResource;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $optionResource;

    protected function setUp()
    {
        $this->productRepositoryMock = $this->getMock('\Magento\Catalog\Api\ProductRepositoryInterface');
        $this->productMock = $this->getMock('\Magento\Catalog\Api\Data\ProductInterface');
        $this->configurableTypeResource = $this->getMockBuilder(
            'Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable'
        )
            ->disableOriginalConstructor()
            ->getMock();
        $this->optionResource = $this->getMockBuilder(
            'Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\Attribute'
        )
            ->disableOriginalConstructor()
            ->getMock();
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $objectManager->getObject(
            '\Magento\ConfigurableProduct\Model\OptionRepository',
            [
                'productRepository' => $this->productRepositoryMock,
                'configurableTypeResource' => $this->configurableTypeResource,
                'optionResource' => $this->optionResource,
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
            ->willReturn(Configurable::TYPE_CODE);

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
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage Only implemented for configurable product: 3
     */
    public function testGetNotProductById()
    {
        $productId = 3;

        $option = $this->getMockBuilder('Magento\ConfigurableProduct\Api\Data\OptionInterface')
            ->disableOriginalConstructor()
            ->setMethods(['getProductId'])
            ->getMockForAbstractClass();
        $option->expects($this->once())
            ->method('getProductId')
            ->willReturn($productId);

        $this->productRepositoryMock->expects($this->once())
            ->method('getById')
            ->with($productId)
            ->willReturn($this->productMock);
        $this->productMock->expects($this->once())
            ->method('getTypeId')
            ->willReturn('simple');

        $this->model->delete($option);
    }

    /**
     * @expectedException \Magento\Framework\Exception\StateException
     * @expectedExceptionMessage Cannot delete variations from product: 3
     */
    public function testDeleteCantSaveProducts()
    {
        $productId = 3;

        $option = $this->getMockBuilder('Magento\ConfigurableProduct\Api\Data\OptionInterface')
            ->disableOriginalConstructor()
            ->setMethods(['getProductId'])
            ->getMockForAbstractClass();
        $option->expects($this->any())
            ->method('getProductId')
            ->willReturn($productId);

        $this->productRepositoryMock->expects($this->once())
            ->method('getById')
            ->with($productId)
            ->willReturn($this->productMock);
        $this->productMock->expects($this->once())
            ->method('getTypeId')
            ->willReturn(Configurable::TYPE_CODE);

        $this->configurableTypeResource->expects($this->once())
            ->method('saveProducts')
            ->with($this->productMock)
            ->willThrowException(new \Exception());
        $this->model->delete($option);
    }

    /**
     * @expectedException \Magento\Framework\Exception\StateException
     * @expectedExceptionMessage Cannot delete option with id: 33
     */
    public function testDeleteCantDeleteOption()
    {
        $productId = 3;
        $optionId = 33;

        $option = $this->getMockBuilder('Magento\ConfigurableProduct\Model\Product\Type\Configurable\Attribute')
            ->disableOriginalConstructor()
            ->setMethods(['getProductId', 'getId'])
            ->getMockForAbstractClass();
        $option->expects($this->any())
            ->method('getProductId')
            ->willReturn($productId);
        $option->expects($this->once())
            ->method('getId')
            ->willReturn($optionId);

        $this->productRepositoryMock->expects($this->once())
            ->method('getById')
            ->with($productId)
            ->willReturn($this->productMock);
        $this->productMock->expects($this->once())
            ->method('getTypeId')
            ->willReturn(Configurable::TYPE_CODE);

        $this->configurableTypeResource->expects($this->once())
            ->method('saveProducts')
            ->with($this->productMock);
        $this->optionResource->expects($this->once())
            ->method('delete')
            ->with($option)
            ->willThrowException(new \Exception());
        $this->model->delete($option);
    }

    public function testDelete()
    {
        $productId = 3;
        $optionId = 33;

        $option = $this->getMockBuilder('Magento\ConfigurableProduct\Model\Product\Type\Configurable\Attribute')
            ->disableOriginalConstructor()
            ->setMethods(['getProductId', 'getId'])
            ->getMockForAbstractClass();
        $option->expects($this->any())
            ->method('getProductId')
            ->willReturn($productId);
        $option->expects($this->any())
            ->method('getId')
            ->willReturn($optionId);

        $this->productRepositoryMock->expects($this->once())
            ->method('getById')
            ->with($productId)
            ->willReturn($this->productMock);
        $this->productMock->expects($this->once())
            ->method('getTypeId')
            ->willReturn(Configurable::TYPE_CODE);

        $this->configurableTypeResource->expects($this->once())
            ->method('saveProducts')
            ->with($this->productMock);
        $this->optionResource->expects($this->once())
            ->method('delete')
            ->with($option);
        $result = $this->model->delete($option);
        $this->assertTrue($result);
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
            ->willReturn(Configurable::TYPE_CODE);

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
            ->willReturn(Configurable::TYPE_CODE);

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
            ->willReturn(Configurable::TYPE_CODE);

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
            ->willReturn(Configurable::TYPE_CODE);

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
        ];
    }
}
