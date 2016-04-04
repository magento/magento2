<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\Product;

class ValidatorTest extends \PHPUnit_Framework_TestCase
{
    public function testValidator()
    {
        $validator = new \Magento\Catalog\Model\Product\Validator();
        $productMock = $this->getMock('Magento\Catalog\Model\Product', [], [], '', false);
        $requestMock = $this->getMock('Magento\Framework\App\RequestInterface');
        $responseMock = $this->getMock('Magento\Framework\DataObject');
        $productMock->expects($this->once())->method('validate')->will($this->returnValue(true));
        $this->assertEquals(true, $validator->validate($productMock, $requestMock, $responseMock));
    }
}
