<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Eav\Test\Unit\Model\Entity\Attribute\Backend;

use Magento\Eav\Model\Entity\Attribute\Backend\JsonEncoded;

class JsonEncodedTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Eav\Model\Entity\Attribute\Backend\JsonEncoded
     */
    private $model;

    /**
     * @var \Magento\Eav\Model\Entity\Attribute|\PHPUnit_Framework_MockObject_MockObject
     */
    private $attributeMock;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json|\PHPUnit_Framework_MockObject_MockObject
     */
    private $serializerMock;

    /**
     * Set up before test
     */
    protected function setUp()
    {
        $this->serializerMock = $this->getMockBuilder(\Magento\Framework\Serialize\Serializer\Json::class)
            ->disableOriginalConstructor()
            ->setMethods(['serialize', 'unserialize'])
            ->getMock();

        $this->serializerMock->expects($this->any())
            ->method('serialize')
            ->will(
                $this->returnCallback(
                    function ($value) {
                        return json_encode($value);
                    }
                )
            );

        $this->serializerMock->expects($this->any())
            ->method('unserialize')
            ->will(
                $this->returnCallback(
                    function ($value) {
                        return json_decode($value, true);
                    }
                )
            );

        $this->attributeMock = $this->getMockBuilder(\Magento\Eav\Model\Entity\Attribute::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAttributeCode'])
            ->getMock();

        $this->attributeMock->expects($this->any())
            ->method('getAttributeCode')
            ->will($this->returnValue('json_encoded'));

        $this->model = new JsonEncoded($this->serializerMock);
        $this->model->setAttribute($this->attributeMock);
    }

    /**
     * Test before save handler
     */
    public function testBeforeSave()
    {
        $product = new \Magento\Framework\DataObject(
            [
                'json_encoded' => [1, 2, 3]
            ]
        );
        $this->model->beforeSave($product);
        $this->assertEquals(json_encode([1, 2, 3]), $product->getData('json_encoded'));
    }

    /**
     * Test after load handler
     */
    public function testAfterLoad()
    {
        $product = new \Magento\Framework\DataObject(
            [
                'json_encoded' => json_encode([1, 2, 3])
            ]
        );
        $this->model->afterLoad($product);
        $this->assertEquals([1, 2, 3], $product->getData('json_encoded'));
    }
}
