<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;
use Magento\Framework\Stdlib\ArrayManager;

/**
 * Class AbstractDataProviderTest
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 */
abstract class AbstractModifierTest extends \PHPUnit\Framework\TestCase
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
     * @var LocatorInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $locatorMock;

    /**
     * @var ProductInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $productMock;

    /**
     * @var StoreInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $storeMock;

    /**
     * @var ArrayManager|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $arrayManagerMock;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->locatorMock = $this->getMockBuilder(LocatorInterface::class)
            ->getMockForAbstractClass();
        $this->productMock = $this->getMockBuilder(ProductInterface::class)
            ->setMethods([
                'getId',
                'getTypeId',
                'getStoreId',
                'getResource',
                'getData',
                'getAttributes',
                'getStore',
                'getAttributeDefaultValue',
                'getExistsStoreValueFlag',
                'isLockedAttribute'
            ])->getMockForAbstractClass();
        $this->storeMock = $this->getMockBuilder(StoreInterface::class)
            ->setMethods(['load', 'getId', 'getConfig'])
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
