<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\Product\CopyConstructor;

class CompositeTest extends \PHPUnit\Framework\TestCase
{
    public function testBuild()
    {
        $factoryMock = $this->createMock(\Magento\Catalog\Model\Product\CopyConstructorFactory::class);

        $constructorMock = $this->createMock(\Magento\Catalog\Model\Product\CopyConstructorInterface::class);

        $factoryMock->expects(
            $this->exactly(2)
        )->method(
            'create'
        )->with(
            'constructorInstance'
        )->will(
            $this->returnValue($constructorMock)
        );

        $productMock = $this->createMock(\Magento\Catalog\Model\Product::class);
        $duplicateMock = $this->createMock(\Magento\Catalog\Model\Product::class);

        $constructorMock->expects($this->exactly(2))->method('build')->with($productMock, $duplicateMock);

        $model = new \Magento\Catalog\Model\Product\CopyConstructor\Composite(
            $factoryMock,
            ['constructorInstance', 'constructorInstance']
        );

        $model->build($productMock, $duplicateMock);
    }
}
