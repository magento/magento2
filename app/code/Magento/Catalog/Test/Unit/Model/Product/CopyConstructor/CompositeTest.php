<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\Product\CopyConstructor;

class CompositeTest extends \PHPUnit_Framework_TestCase
{
    public function testBuild()
    {
        $factoryMock = $this->getMock(
            \Magento\Catalog\Model\Product\CopyConstructorFactory::class,
            [],
            [],
            '',
            false
        );

        $constructorMock = $this->getMock(\Magento\Catalog\Model\Product\CopyConstructorInterface::class);

        $factoryMock->expects(
            $this->exactly(2)
        )->method(
            'create'
        )->with(
            'constructorInstance'
        )->will(
            $this->returnValue($constructorMock)
        );

        $productMock = $this->getMock(\Magento\Catalog\Model\Product::class, [], [], '', false);
        $duplicateMock = $this->getMock(\Magento\Catalog\Model\Product::class, [], [], '', false);

        $constructorMock->expects($this->exactly(2))->method('build')->with($productMock, $duplicateMock);

        $model = new \Magento\Catalog\Model\Product\CopyConstructor\Composite(
            $factoryMock,
            ['constructorInstance', 'constructorInstance']
        );

        $model->build($productMock, $duplicateMock);
    }
}
