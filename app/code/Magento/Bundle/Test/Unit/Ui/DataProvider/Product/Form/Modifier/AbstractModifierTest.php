<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Test\Unit\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

abstract class AbstractModifierTest extends TestCase
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
        $this->locatorMock = $this->getMockBuilder(LocatorInterface::class)
            ->getMockForAbstractClass();
        $this->productMock = $this->getMockBuilder(ProductInterface::class)
            ->addMethods(['getPriceType'])
            ->getMockForAbstractClass();

        $this->locatorMock->expects($this->any())
            ->method('getProduct')
            ->willReturn($this->productMock);

        $this->arrayManagerMock = $this->getMockBuilder(ArrayManager::class)
            ->disableOriginalConstructor()
            ->getMock();
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
