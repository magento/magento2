<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\Store;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Abstract test case for product form modifiers
 *
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.LongMethod)
 */
abstract class AbstractModifierTestCase extends TestCase
{
    use MockCreationTrait;

    /**
     * @var ModifierInterface
     */
    private $model;

    /**
     * @var LocatorInterface|MockObject
     */
    protected $locatorMock;

    /**
     * @var Product|MockObject
     */
    protected $productMock;

    /**
     * @var Store|MockObject
     */
    protected $storeMock;

    /**
     * @var ArrayManager|MockObject
     */
    protected $arrayManagerMock;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->locatorMock = $this->createMock(LocatorInterface::class);
        
        // Use createPartialMock for Product class to allow method configuration
        // Note: getId/setId are NOT mocked, allowing natural behavior via DataObject
        $this->productMock = $this->createPartialMock(
            Product::class,
            [
                'getCustomAttributesCodes',
                'getStoreId',
                'getResource',
                'isLockedAttribute',
                'getTypeId',
                'getAttributeSetId',
                'getOptions',
                'getStore',
                'getMediaAttributes'
            ]
        );
        $this->productMock->method('getCustomAttributesCodes')->willReturn([]);
        $this->productMock->method('getMediaAttributes')->willReturn([]);

        $this->storeMock = $this->createPartialMock(Store::class, ['load', 'getConfig', 'getId']);
        $this->storeMock->method('getId')->willReturn(1);

        $this->arrayManagerMock = $this->createMock(ArrayManager::class);

        $this->arrayManagerMock->expects($this->any())
            ->method('replace')
            ->willReturnArgument(1);
        $this->arrayManagerMock->expects($this->any())
            ->method('get')
            ->willReturnArgument(2);
        $this->arrayManagerMock->expects($this->any())
            ->method('set')
            ->willReturnArgument(1);
        $this->arrayManagerMock->expects($this->any())
            ->method('remove')
            ->willReturnArgument(1);

        $this->locatorMock->method('getProduct')->willReturn($this->productMock);
        $this->locatorMock->method('getStore')->willReturn($this->storeMock);
    }

    /**
     * @return ModifierInterface
     */
    abstract protected function createModel();

    /**
     * @return ModifierInterface
     */
    protected function getModel()
    {
        if (null === $this->model) {
            $this->model = $this->createModel();
        }

        return $this->model;
    }

    /**
     * @return array
     */
    protected function getSampleData()
    {
        return ['data_key' => 'data_value'];
    }
}
