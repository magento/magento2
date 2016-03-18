<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogImportExport\Test\Unit\Model\Import\Product;

use Magento\CatalogImportExport\Model\Import\Product\SkuProcessor as SkuProcessor;

class SkuProcessorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\ProductFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productFactory;

    /**
     * @var SkuProcessor|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $skuProcessor;

    protected function setUp()
    {
        $this->productFactory = $this->getMock(\Magento\Catalog\Model\ProductFactory::class, [], [], '', false);
        $this->skuProcessor = $this->getMock(
            \Magento\CatalogImportExport\Model\Import\Product\SkuProcessor::class,
            ['_getSkus'],
            [
                $this->productFactory,
            ],
            ''
        );
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
