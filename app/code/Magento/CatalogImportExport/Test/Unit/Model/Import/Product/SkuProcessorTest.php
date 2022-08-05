<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogImportExport\Test\Unit\Model\Import\Product;

use Magento\CatalogImportExport\Model\Import\Product\SkuProcessor as SkuProcessor;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SkuProcessorTest extends TestCase
{
    /**
     * @var \Magento\Catalog\Model\ProductFactory|MockObject
     */
    protected $productFactory;

    /**
     * @var SkuProcessor|MockObject
     */
    protected $skuProcessor;

    protected function setUp(): void
    {
        $this->productFactory = $this->createMock(\Magento\Catalog\Model\ProductFactory::class);
        $this->skuProcessor = $this->getMockBuilder(
            \Magento\CatalogImportExport\Model\Import\Product\SkuProcessor::class
        )
            ->setMethods(['_getSkus'])
            ->setConstructorArgs([$this->productFactory])
            ->getMock();
    }

    public function testReloadOldSkus()
    {
        $skuValue = 'value';

        $this->skuProcessor
            ->expects($this->once())
            ->method('_getSkus')
            ->willReturn($skuValue);

        $this->skuProcessor->reloadOldSkus();
        $oldSkus = $this->getPropertyValue($this->skuProcessor, 'oldSkus');

        $this->assertEquals($skuValue, $oldSkus);
    }

    public function testGetOldSkusIfNotSet()
    {
        $expectedOldSkus = 'value';
        $this->setPropertyValue($this->skuProcessor, 'oldSkus', null);
        $this->skuProcessor
            ->expects($this->once())
            ->method('_getSkus')
            ->willReturn($expectedOldSkus);

        $result = $this->skuProcessor->getOldSkus();

        $this->assertEquals($expectedOldSkus, $result);
    }

    public function testGetOldSkusIfSet()
    {
        $expectedOldSkus = 'value';
        $this->setPropertyValue($this->skuProcessor, 'oldSkus', 'value');
        $this->skuProcessor
            ->expects($this->never())
            ->method('_getSkus');

        $result = $this->skuProcessor->getOldSkus();

        $this->assertEquals($expectedOldSkus, $result);
    }

    /**
     * Set object property.
     *
     * @param object $object
     * @param string $property
     * @param mixed $value
     */
    protected function setPropertyValue(&$object, $property, $value)
    {
        $reflection = new \ReflectionClass(get_class($object));
        $reflectionProperty = $reflection->getProperty($property);
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($object, $value);

        return $object;
    }

    /**
     * Get object property.
     *
     * @param object $object
     * @param string $property
     */
    protected function getPropertyValue(&$object, $property)
    {
        $reflection = new \ReflectionClass(get_class($object));
        $reflectionProperty = $reflection->getProperty($property);
        $reflectionProperty->setAccessible(true);

        return $reflectionProperty->getValue($object);
    }
}
