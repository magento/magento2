<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Test\Unit\Model\Product;

use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Api\Data\OptionValueInterface;
use Magento\ConfigurableProduct\Api\Data\OptionValueInterfaceFactory;
use Magento\ConfigurableProduct\Model\Product\ReadHandler;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable\Attribute;
use Magento\Framework\Model\Entity\EntityMetadata;
use Magento\Framework\Model\Entity\MetadataPool;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Class ReadHandlerTest
 */
class ReadHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var OptionValueInterfaceFactory|MockObject
     */
    private $optionValueFactory;

    /**
     * @var MetadataPool|MockObject
     */
    private $metadataPool;

    /**
     * @var EntityMetadata|MockObject
     */
    private $metadata;

    /**
     * @var ReadHandler
     */
    private $readHandler;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->optionValueFactory = $this->getMockBuilder(OptionValueInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->initMetadataPoolMock();

        $this->readHandler = new ReadHandler($this->optionValueFactory, $this->metadataPool);
    }

    /**
     * Init mock object for metadata pool
     *
     * @return void
     */
    private function initMetadataPoolMock()
    {
        $this->metadata = $this->getMockBuilder(EntityMetadata::class)
            ->disableOriginalConstructor()
            ->setMethods(['getLinkField'])
            ->getMock();

        $this->metadataPool = $this->getMockBuilder(MetadataPool::class)
            ->disableOriginalConstructor()
            ->setMethods(['getMetadata'])
            ->getMock();

        $this->metadataPool->expects(static::any())
            ->method('getMetadata')
            ->willReturn($this->metadata);
    }

    /**
     * @covers \Magento\ConfigurableProduct\Model\Product\ReadHandler::execute
     */
    public function testExecuteWithInvalidProductType()
    {
        $product = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->setMethods(['getTypeId', 'getExtensionAttributes'])
            ->getMock();

        $product->expects(static::once())
            ->method('getTypeId')
            ->willReturn('simple');

        $product->expects(static::never())
            ->method('getExtensionAttributes');

        $entity = $this->readHandler->execute('Entity', $product);
        static::assertSame($product, $entity);
    }

    /**
     * @covers \Magento\ConfigurableProduct\Model\Product\ReadHandler::execute
     */
    public function testExecute()
    {
        $options = [
            ['value_index' => 12],
            ['value_index' => 13]
        ];
        $linkField = 'entity_id';
        $entityId = 1;
        $ids = [1, 2, 3];

        $product = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getTypeId', 'getData', 'getExtensionAttributes', 'setExtensionAttributes', 'getTypeInstance'
            ])
            ->getMock();

        $product->expects(static::once())
            ->method('getTypeId')
            ->willReturn(Configurable::TYPE_CODE);

        $extensionAttributes = $this->getMockBuilder(PaymentExtensionAttributes::class)
            ->disableOriginalConstructor()
            ->setMethods(['setConfigurableProductOptions', 'setConfigurableProductLinks'])
            ->getMockForAbstractClass();

        $product->expects(static::once())
            ->method('getExtensionAttributes')
            ->willReturn($extensionAttributes);

        $typeInstance = $this->getMockBuilder(Configurable::class)
            ->disableOriginalConstructor()
            ->setMethods(['getConfigurableAttributes', 'getChildrenIds'])
            ->getMock();

        $attribute = $this->getMockBuilder(Attribute::class)
            ->disableOriginalConstructor()
            ->setMethods(['getOptions', 'setValues'])
            ->getMock();
        $typeInstance->expects(static::once())
            ->method('getConfigurableAttributes')
            ->with($product)
            ->willReturn([$attribute]);

        $product->expects(static::exactly(2))
            ->method('getTypeInstance')
            ->willReturn($typeInstance);

        $attribute->expects(static::once())
            ->method('getOptions')
            ->willReturn($options);

        $optionValue = $this->getMock(OptionValueInterface::class);
        $this->optionValueFactory->expects(static::exactly(sizeof($options)))
            ->method('create')
            ->willReturn($optionValue);
        $optionValue->expects(static::exactly(2))
            ->method('setValueIndex');
        $attribute->expects(static::once())
            ->method('setValues');

        $this->metadata->expects(static::once())
            ->method('getLinkField')
            ->willReturn($linkField);
        $product->expects(static::once())
            ->method('getData')
            ->with($linkField)
            ->willReturn($entityId);

        $typeInstance->expects(static::once())
            ->method('getChildrenIds')
            ->willReturn($ids);

        $product->expects(static::once())
            ->method('setExtensionAttributes')
            ->with($extensionAttributes);

        $entity = $this->readHandler->execute('Entity', $product);
        static::assertSame($product, $entity);
    }
}
