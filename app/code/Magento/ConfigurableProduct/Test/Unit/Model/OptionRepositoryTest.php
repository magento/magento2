<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Test\Unit\Model;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\ConfigurableProduct\Api\Data\OptionInterface;
use Magento\ConfigurableProduct\Api\Data\OptionValueInterface;
use Magento\ConfigurableProduct\Helper\Product\Options\Loader;
use Magento\ConfigurableProduct\Model\OptionRepository;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable\Attribute;
use Magento\Framework\Exception\InputException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class OptionRepositoryTest extends TestCase
{
    /**
     * @var OptionRepository
     */
    protected $model;

    /**
     * @var MockObject
     */
    protected $productRepositoryMock;

    /**
     * @var MockObject
     */
    protected $productMock;

    /**
     * @var MockObject
     */
    protected $configurableTypeResource;

    /**
     * @var MockObject
     */
    protected $optionResource;

    /**
     * @var Loader|MockObject
     */
    private $optionLoader;

    protected function setUp(): void
    {
        $this->productRepositoryMock = $this->getMockBuilder(ProductRepositoryInterface::class)
            ->getMockForAbstractClass();

        $this->productMock = $this->getMockForAbstractClass(ProductInterface::class);

        $this->configurableTypeResource = $this->getMockBuilder(
            \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable::class
        )->disableOriginalConstructor()
            ->getMock();

        $this->optionResource = $this->getMockBuilder(
            \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\Attribute::class
        )->disableOriginalConstructor()
            ->getMock();

        $this->optionLoader = $this->getMockBuilder(Loader::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new ObjectManager($this);
        $this->model = $objectManager->getObject(
            OptionRepository::class,
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

        $optionMock = $this->getMockForAbstractClass(OptionInterface::class);
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

    public function testGetNotConfigurableProduct()
    {
        $this->expectException('Magento\Framework\Exception\InputException');
        $this->expectExceptionMessage('This is implemented for the "configurable" configurable product only.');
        $productSku = "configurable";
        $optionId = 3;

        $this->productMock->expects(self::once())
            ->method('getTypeId')
            ->willReturn(Configurable::TYPE_CODE . '-not');

        $this->productRepositoryMock->expects(self::once())
            ->method('get')
            ->with($productSku)
            ->willReturn($this->productMock);

        $optionMock = $this->getMockForAbstractClass(OptionInterface::class);
        $optionMock->expects(self::never())
            ->method('getId');

        $this->optionLoader->expects(self::never())
            ->method('load');

        $this->model->get($productSku, $optionId);
    }

    public function testGetNotProductById()
    {
        $this->expectException('Magento\Framework\Exception\InputException');
        $this->expectExceptionMessage('This is implemented for the "3" configurable product only.');
        $entityId = 3;
        /** @var OptionInterface $optionMock */
        $optionMock = $this->getMockForAbstractClass(OptionInterface::class);

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

    public function testDeleteCantSaveProducts()
    {
        $this->expectException('Magento\Framework\Exception\StateException');
        $this->expectExceptionMessage('The variations from the "3" product can\'t be deleted.');
        $entityId = 3;
        /** @var OptionInterface $optionMock */
        $optionMock = $this->getMockForAbstractClass(OptionInterface::class);

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

    public function testDeleteCantDeleteOption()
    {
        $this->expectException('Magento\Framework\Exception\StateException');
        $this->expectExceptionMessage('The option with "33" ID can\'t be deleted.');
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

    public function testGetEmptyExtensionAttribute()
    {
        $this->expectException('Magento\Framework\Exception\NoSuchEntityException');
        $this->expectExceptionMessage(
            'The "3" entity that was requested doesn\'t exist. Verify the entity and try again.'
        );
        $optionId = 3;
        $productSku = "configurable";

        $this->productMock->expects(self::once())
            ->method('getTypeId')
            ->willReturn(Configurable::TYPE_CODE);

        $this->productRepositoryMock->expects(self::once())
            ->method('get')
            ->with($productSku)
            ->willReturn($this->productMock);

        $optionMock = $this->getMockForAbstractClass(OptionInterface::class);
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

        $optionMock = $this->getMockForAbstractClass(OptionInterface::class);

        $this->optionLoader->expects(self::once())
            ->method('load')
            ->with($this->productMock)
            ->willReturn([$optionMock]);

        $this->assertEquals([$optionMock], $this->model->getList($productSku));
    }

    public function testGetListNotConfigurableProduct()
    {
        $this->expectException('Magento\Framework\Exception\InputException');
        $this->expectExceptionMessage('This is implemented for the "configurable" configurable product only.');
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
     * @throws InputException
     */
    public function testValidateNewOptionData($attributeId, $label, $optionValues, $msg)
    {
        $this->expectException(InputException::class);
        $this->expectExceptionMessage($msg);
        $optionValueMock = $this->getMockBuilder(OptionValueInterface::class)
            ->addMethods(['getPricingValue', 'getIsPercent'])
            ->onlyMethods(['getValueIndex'])
            ->getMockForAbstractClass();
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

        $optionMock = $this->getMockForAbstractClass(OptionInterface::class);
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

    /**
     * @return array
     */
    public static function validateOptionDataProvider()
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
