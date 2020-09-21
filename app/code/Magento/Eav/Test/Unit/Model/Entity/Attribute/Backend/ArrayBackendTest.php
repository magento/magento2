<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Eav\Test\Unit\Model\Entity\Attribute\Backend;

use Magento\Eav\Model\Entity\Attribute;
use Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend;
use Magento\Framework\DataObject;
use PHPUnit\Framework\TestCase;

class ArrayBackendTest extends TestCase
{
    /**
     * @var ArrayBackend
     */
    protected $_model;

    /**
     * @var Attribute
     */
    protected $_attribute;

    protected function setUp(): void
    {
        $this->_attribute = $this->createPartialMock(
            Attribute::class,
            ['getAttributeCode', '__wakeup']
        );
        $this->_model = new ArrayBackend();
        $this->_model->setAttribute($this->_attribute);
    }

    /**
     * @dataProvider attributeValueDataProvider
     */
    public function testValidate($data)
    {
        $this->_attribute->expects($this->atLeastOnce())->method('getAttributeCode')->willReturn('code');
        $product = new DataObject(['code' => $data, 'empty' => null]);
        $this->_model->validate($product);
        $this->assertEquals('1,2,3', $product->getCode());
        $this->assertNull($product->getEmpty());
    }

    /**
     * @return array
     */
    public static function attributeValueDataProvider()
    {
        return [[[1, 2, 3]], ['1,2,3']];
    }
}
