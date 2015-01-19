<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Model\Entity\Attribute\Backend;

class ArrayTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend
     */
    protected $_model;

    /**
     * @var \Magento\Eav\Model\Entity\Attribute
     */
    protected $_attribute;

    protected function setUp()
    {
        $this->_attribute = $this->getMock(
            'Magento\Eav\Model\Entity\Attribute',
            ['getAttributeCode', '__wakeup'],
            [],
            '',
            false
        );
        $logger = $this->getMock('Psr\Log\LoggerInterface');
        $this->_model = new \Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend($logger);
        $this->_model->setAttribute($this->_attribute);
    }

    /**
     * @dataProvider attributeValueDataProvider
     */
    public function testValidate($data)
    {
        $this->_attribute->expects($this->atLeastOnce())->method('getAttributeCode')->will($this->returnValue('code'));
        $product = new \Magento\Framework\Object(['code' => $data]);
        $this->_model->validate($product);
        $this->assertEquals('1,2,3', $product->getCode());
    }

    public static function attributeValueDataProvider()
    {
        return [[[1, 2, 3]], ['1,2,3']];
    }
}
