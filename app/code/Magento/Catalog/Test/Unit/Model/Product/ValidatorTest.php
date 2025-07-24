<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Product;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Validator;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\DataObject;
use PHPUnit\Framework\TestCase;

class ValidatorTest extends TestCase
{
    public function testValidator()
    {
        $validator = new Validator();
        $productMock = $this->createMock(Product::class);
        $requestMock = $this->getMockForAbstractClass(RequestInterface::class);
        $responseMock = $this->createMock(DataObject::class);
        $productMock->expects($this->once())->method('validate')->willReturn(true);
        $this->assertTrue($validator->validate($productMock, $requestMock, $responseMock));
    }
}
