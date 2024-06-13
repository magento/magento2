<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Test\Unit\Helper\ProductTestHelper;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Test\Unit\Helper\StoreTestHelper;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Abstract test case for product form modifiers
 *
 * Uses test helpers instead of inline anonymous classes following PHPUnit 12 migration rules.
 *
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.LongMethod)
 */
abstract class AbstractModifierTestCase extends TestCase
{
    /**
     * @var ModifierInterface
     */
    private $model;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var LocatorInterface|MockObject
     */
    protected $locatorMock;

    /**
     * @var ProductTestHelper
     */
    protected $productMock;

    /**
     * @var StoreTestHelper
     */
    protected $storeMock;

    /**
     * @var ArrayManager|MockObject
     */
    protected $arrayManagerMock;

    /**
     * Set up test environment
     *
     * @return void
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->locatorMock = $this->createMock(LocatorInterface::class);
        $this->productMock = new ProductTestHelper();
        $this->storeMock = new StoreTestHelper();
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
