<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\Product;

class ValidatorTest extends \PHPUnit\Framework\TestCase
{
    public function testValidator()
    {
        $validator = new \Magento\Catalog\Model\Product\Validator();
        $productMock = $this->createMock(\Magento\Catalog\Model\Product::class);
        $requestMock = $this->createMock(\Magento\Framework\App\RequestInterface::class);
        $responseMock = $this->createMock(\Magento\Framework\DataObject::class);
        $productMock->expects($this->once())->method('validate')->willReturn(true);
        $this->assertTrue($validator->validate($productMock, $requestMock, $responseMock));
    }
}
