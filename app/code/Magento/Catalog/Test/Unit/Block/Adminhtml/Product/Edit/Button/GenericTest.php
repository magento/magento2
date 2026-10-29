<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Block\Adminhtml\Product\Edit\Button;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Block\Adminhtml\Product\Edit\Button\Generic;
use Magento\Framework\Registry;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Element\UiComponent\Context;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GenericTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var Context|MockObject
     */
    protected $contextMock;

    /**
     * @var Registry|MockObject
     */
    protected $registryMock;

    /**
     * @var Product|MockObject
     */
    protected $productMock;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->contextMock = $this->createMock(Context::class);
        $this->registryMock = $this->createMock(Registry::class);
        // Create a mock that implements ProductInterface with the required methods
        $this->productMock = $this->createPartialMock(Product::class, ['isReadonly', 'isDuplicable']);

        $this->registryMock->expects($this->any())
            ->method('registry')
            ->with('current_product')
            ->willReturn($this->productMock);
    }

    /**
     * @param string $class
     * @return Generic
     */
    protected function getModel($class = Generic::class)
    {
        return $this->objectManager->getObject($class, [
            'context' => $this->contextMock,
            'registry' => $this->registryMock,
        ]);
    }

    public function testGetUrl()
    {
        $this->contextMock->expects($this->once())
            ->method('getUrl')
            ->willReturn('test_url');

        $this->assertSame('test_url', $this->getModel()->getUrl());
    }

    public function testGetProduct()
    {
        $this->assertInstanceOf(Product::class, $this->getModel()->getProduct());
    }
}
