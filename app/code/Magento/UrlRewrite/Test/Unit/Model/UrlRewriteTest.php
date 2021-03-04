<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\UrlRewrite\Test\Unit\Model;

class UrlRewriteTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\UrlRewrite\Model\UrlRewrite
     */
    protected $model;

    protected function setUp(): void
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $context = $this->createMock(\Magento\Framework\Model\Context::class);
        $registry = $this->createMock(\Magento\Framework\Registry::class);
        $resource = $this->createPartialMock(
            \Magento\Framework\Model\ResourceModel\AbstractResource::class,
            ['getIdFieldName', '_construct', 'getConnection']
        );
        $resourceCollection = $this->createMock(\Magento\Framework\Data\Collection\AbstractDb::class);
        $serializer = $this->createMock(\Magento\Framework\Serialize\Serializer\Json::class);
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
