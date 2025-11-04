<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Bundle\Test\Unit\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Test\Unit\Helper\ProductTestHelper;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

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
     * @var ProductInterface|MockObject
     */
    protected $productMock;

    /**
     * @var ArrayManager|MockObject
     */
    protected $arrayManagerMock;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->locatorMock = $this->createMock(LocatorInterface::class);
        
        // Use ProductTestHelper to support custom methods like getPriceType
        $this->productMock = new ProductTestHelper();

        $this->locatorMock->method('getProduct')->willReturn($this->productMock);

        $this->arrayManagerMock = $this->createMock(ArrayManager::class);
        $this->arrayManagerMock->expects($this->any())
            ->method('get')
            ->willReturnArgument(3);
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
}
