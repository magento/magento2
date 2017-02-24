<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Controller\Adminhtml\Product\Initialization\Helper\Plugin\Handler;

use \Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper\Plugin\Handler\Composite;

class CompositeTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        $factoryMock = $this->getMock(
            \Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper\HandlerFactory::class,
            [],
            [],
            '',
            false
        );

        $constructorMock = $this->getMock(
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

        $productMock = $this->getMock(\Magento\Catalog\Model\Product::class, [], [], '', false);

        $constructorMock->expects($this->exactly(2))->method('handle')->with($productMock);

        $model = new Composite($factoryMock, ['handlerInstance', 'handlerInstance']);

        $model->handle($productMock);
    }
}
