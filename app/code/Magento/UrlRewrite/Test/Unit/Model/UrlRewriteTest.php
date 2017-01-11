<?php
/**
 * Copyright © 2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\UrlRewrite\Test\Unit\Model;

class UrlRewriteTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\UrlRewrite\Model\UrlRewrite
     */
    protected $model;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $context = $this->getMock(\Magento\Framework\Model\Context::class, [], [], '', false);
        $registry = $this->getMock(\Magento\Framework\Registry::class, [], [], '', false);
        $resource = $this->getMock(
            \Magento\Framework\Model\ResourceModel\AbstractResource::class,
            ['getIdFieldName', '_construct', 'getConnection'],
            [],
            '',
            false
        );
        $resourceCollection = $this->getMock(\Magento\Framework\Data\Collection\AbstractDb::class, [], [], '', false);
        $serializer = $this->getMock(\Magento\Framework\Serialize\SerializerInterface::class, [], [], '', false);
        $serializer->expects($this->any())
            ->method('serialize')
            ->willReturnCallback(
                function ($value) {
                    return json_encode($value);
                }
            );
        $serializer->expects($this->any())
            ->method('unserialize')
            ->willReturnCallback(
                function ($value) {
                    return json_decode($value, true);
                }
            );

        $this->model = $objectManager->getObject(
            \Magento\UrlRewrite\Model\UrlRewrite::class,
            [
                'context' => $context,
                'registry' => $registry,
                'resource' => $resource,
                'resourceCollection' => $resourceCollection,
                'data' => [],
                'serializer' => $serializer,
            ]
        );
    }

    public function testSetAndGetMetadata()
    {
        $testData = [1, 2, 3];

        $this->model->setMetadata($testData);

        $this->assertEquals($testData, $this->model->getMetadata());
    }
}
