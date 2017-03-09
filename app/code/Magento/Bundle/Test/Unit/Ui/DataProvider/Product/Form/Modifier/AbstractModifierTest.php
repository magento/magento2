<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Test\Unit\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;
use Magento\Framework\Stdlib\ArrayManager;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

abstract class AbstractModifierTest extends \PHPUnit_Framework_TestCase
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

    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);
        $this->locatorMock = $this->getMockBuilder(LocatorInterface::class)
            ->getMockForAbstractClass();
        $this->productMock = $this->getMockBuilder(ProductInterface::class)
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
