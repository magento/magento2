<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Block\Adminhtml\Product\Edit\Button;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Registry;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Element\UiComponent\Context;
use Magento\Catalog\Block\Adminhtml\Product\Edit\Button\Generic;

/**
 * Class GenericTest
 */
class GenericTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $contextMock;

    /**
     * @var Registry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $registryMock;

    /**
     * @var ProductInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productMock;

    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);
        $this->contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->registryMock = $this->getMockBuilder(Registry::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->productMock = $this->getMockBuilder(ProductInterface::class)
            ->setMethods(['isReadonly', 'isDuplicable'])
            ->getMockForAbstractClass();

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
        $this->assertInstanceOf(ProductInterface::class, $this->getModel()->getProduct());
    }
}
