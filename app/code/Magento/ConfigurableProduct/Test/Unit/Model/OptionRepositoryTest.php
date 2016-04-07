<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Test\Unit\Model;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\ConfigurableProduct\Api\Data\OptionInterface;
use Magento\ConfigurableProduct\Helper\Product\Options\Loader;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable\Attribute;

/**
 * Class OptionRepositoryTest
 */
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

    /**
     * @var Loader|\PHPUnit_Framework_MockObject_MockObject
     */
    private $optionLoader;

    protected function setUp()
    {
        $this->productRepositoryMock = $this->getMockBuilder(ProductRepositoryInterface::class)
            ->getMockForAbstractClass();

        $this->productMock = $this->getMock('\Magento\Catalog\Api\Data\ProductInterface');

        $this->configurableTypeResource = $this->getMockBuilder(
            'Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable'
        )->disableOriginalConstructor()
            ->getMock();

        $this->optionResource = $this->getMockBuilder(
            'Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\Attribute'
        )->disableOriginalConstructor()
            ->getMock();

        $this->optionLoader = $this->getMockBuilder(Loader::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $objectManager->getObject(
            '\Magento\ConfigurableProduct\Model\OptionRepository',
            [
                'productRepository' => $this->productRepositoryMock,
                'configurableTypeResource' => $this->configurableTypeResource,
                'optionResource' => $this->optionResource,
                'optionLoader' => $this->optionLoader
            ]
        );
    }

    public function testGet()
    {
        $optionId = 3;
        $productSku = "configurable";

        $this->productMock->expects(self::once())
            ->method('getTypeId')
            ->willReturn(Configurable::TYPE_CODE);

        $this->productRepositoryMock->expects(self::once())
            ->method('get')
            ->with($productSku)
            ->willReturn($this->productMock);

        $optionMock = $this->getMock(OptionInterface::class);
        $optionMock->expects(self::once())
            ->method('getId')
            ->willReturn($optionId);

        $this->optionLoader->expects(self::once())
            ->method('load')
            ->with($this->productMock)
            ->willReturn([$optionMock]);

        self::assertEquals(
            $optionMock,
            $this->model->get($productSku, $optionId)
        );
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage Only implemented for configurable product: configurable
     */
    public function testGetNotConfigurableProduct()
    {
        $productSku = "configurable";
        $optionId = 3;

        $this->productMock->expects(self::once())
            ->method('getTypeId')
            ->willReturn(Configurable::TYPE_CODE . '-not');

        $this->productRepositoryMock->expects(self::once())
            ->method('get')
            ->with($productSku)
            ->willReturn($this->productMock);

        $optionMock = $this->getMock(OptionInterface::class);
        $optionMock->expects(self::never())
            ->method('getId');

        $this->optionLoader->expects(self::never())
            ->method('load');

        $this->model->get($productSku, $optionId);
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage Only implemented for configurable product: 3
     */
    public function testGetNotProductById()
    {
        $entityId = 3;
        /** @var OptionInterface $optionMock */
        $optionMock = $this->getMock(OptionInterface::class);

        $this->configurableTypeResource->expects(self::once())
            ->method('getEntityIdByAttribute')
            ->with($optionMock)
            ->willReturn($entityId);

        $this->productRepositoryMock->expects(self::once())
            ->method('getById')
            ->with($entityId)
            ->willReturn($this->productMock);

        $this->productMock->expects(self::once())
            ->method('getTypeId')
            ->willReturn(Configurable::TYPE_CODE . '-not');

        $this->model->delete($optionMock);
    }

    /**
     * @expectedException \Magento\Framework\Exception\StateException
     * @expectedExceptionMessage Cannot delete variations from product: 3
     */
    public function testDeleteCantSaveProducts()
    {
        $entityId = 3;
        /** @var OptionInterface $optionMock */
        $optionMock = $this->getMock(OptionInterface::class);

        $this->configurableTypeResource->expects(self::once())
            ->method('getEntityIdByAttribute')
            ->with($optionMock)
            ->willReturn($entityId);

        $this->productRepositoryMock->expects(self::once())
            ->method('getById')
            ->with($entityId)
            ->willReturn($this->productMock);

        $this->productMock->expects(self::once())
            ->method('getTypeId')
            ->willReturn(Configurable::TYPE_CODE);

        $this->configurableTypeResource->expects(self::once())
            ->method('saveProducts')
            ->with($this->productMock)
            ->willThrowException(new \Exception());

        $this->model->delete($optionMock);
    }

    /**
     * @expectedException \Magento\Framework\Exception\StateException
     * @expectedExceptionMessage Cannot delete option with id: 33
     */
    public function testDeleteCantDeleteOption()
    {
        $entityId = 3;
        $optionMock = $this->getMockBuilder(Attribute::class)
            ->disableOriginalConstructor()
            ->getMock();

        $optionMock->expects(self::once())
            ->method('getId')
            ->willReturn(33);

        $this->configurableTypeResource->expects(self::once())
            ->method('getEntityIdByAttribute')
            ->with($optionMock)
            ->willReturn($entityId);

        $this->productRepositoryMock->expects(self::once())
            ->method('getById')
            ->with($entityId)
            ->willReturn($this->productMock);

        $this->productMock->expects(self::once())
            ->method('getTypeId')
            ->willReturn(Configurable::TYPE_CODE);

        $this->configurableTypeResource->expects(self::once())
            ->method('saveProducts')
            ->with($this->productMock);

        $this->optionResource->expects(self::once())
            ->method('delete')
            ->with($optionMock)
            ->willThrowException(new \Exception());

        $this->model->delete($optionMock);
    }

    public function testDelete()
    {
        $entityId = 3;
        $optionMock = $this->getMockBuilder(Attribute::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->configurableTypeResource->expects(self::once())
            ->method('getEntityIdByAttribute')
            ->with($optionMock)
            ->willReturn($entityId);

        $this->productRepositoryMock->expects(self::once())
            ->method('getById')
            ->with($entityId)
            ->willReturn($this->productMock);

        $this->productMock->expects(self::once())
            ->method('getTypeId')
            ->willReturn(Configurable::TYPE_CODE);

        $this->configurableTypeResource->expects(self::once())
            ->method('saveProducts')
            ->with($this->productMock);

        $this->optionResource->expects(self::once())
            ->method('delete')
            ->with($optionMock)
            ->willReturn(true);

        $result = $this->model->delete($optionMock);

        $this->assertTrue($result);
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage Requested option doesn't exist: 3
     */
    public function testGetEmptyExtensionAttribute()
    {
        $optionId = 3;
        $productSku = "configurable";

        $this->productMock->expects(self::once())
            ->method('getTypeId')
            ->willReturn(Configurable::TYPE_CODE);

        $this->productRepositoryMock->expects(self::once())
            ->method('get')
            ->with($productSku)
            ->willReturn($this->productMock);

        $optionMock = $this->getMock(OptionInterface::class);
        $optionMock->expects(self::never())
            ->method('getId');

        $this->optionLoader->expects(self::once())
            ->method('load')
            ->with($this->productMock)
            ->willReturn([]);

        $this->model->get($productSku, $optionId);
    }

    public function testGetList()
    {
        $productSku = "configurable";

        $this->productMock->expects(self::once())
            ->method('getTypeId')
            ->willReturn(Configurable::TYPE_CODE);

        $this->productRepositoryMock->expects(self::once())
            ->method('get')
            ->with($productSku)
            ->willReturn($this->productMock);

        $optionMock = $this->getMock(OptionInterface::class);

        $this->optionLoader->expects(self::once())
            ->method('load')
            ->with($this->productMock)
            ->willReturn([$optionMock]);

        $this->assertEquals([$optionMock], $this->model->getList($productSku));
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
