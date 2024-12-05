<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.NumberOfChildren)
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
     * @var ProductInterface|MockObject
     */
    protected $productMock;

    /**
     * @var StoreInterface|MockObject
     */
    protected $storeMock;

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
            ->addMethods([
                'getStoreId',
                'getResource',
                'getData',
                'getAttributes',
                'getStore',
                'getAttributeDefaultValue',
                'getExistsStoreValueFlag',
                'isLockedAttribute'
            ])
            ->onlyMethods([
                'getId',
                'getTypeId'
            ])->getMockForAbstractClass();
        $this->storeMock = $this->getMockBuilder(StoreInterface::class)
            ->addMethods(['load', 'getConfig'])
            ->onlyMethods(['getId'])
            ->getMockForAbstractClass();
        $this->arrayManagerMock = $this->getMockBuilder(ArrayManager::class)
            ->disableOriginalConstructor()
            ->getMock();

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

        $this->locatorMock->expects($this->any())
            ->method('getProduct')
            ->willReturn($this->productMock);
        $this->locatorMock->expects($this->any())
            ->method('getStore')
            ->willReturn($this->storeMock);
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
