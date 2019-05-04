<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Controller\Adminhtml\Product\Initialization\Helper\Plugin\Handler;

use Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper\Plugin\Handler\Composite;

class CompositeTest extends \PHPUnit\Framework\TestCase
{
    public function testHandle()
    {
        $factoryMock = $this->createMock(
            \Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper\HandlerFactory::class
        );

        $constructorMock = $this->createMock(
            \Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper\HandlerInterface::class
        );

        $factoryMock->expects(
            $this->exactly(2)
        )->method(
            'create'
        )->with(
            'handlerInstance'
        )->will(
            $this->returnValue($constructorMock)
        );

        $productMock = $this->createMock(\Magento\Catalog\Model\Product::class);

        $constructorMock->expects($this->exactly(2))->method('handle')->with($productMock);

        $model = new Composite($factoryMock, ['handlerInstance', 'handlerInstance']);

        $model->handle($productMock);
    }
}
