<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\UrlRewrite\Test\Unit\Model;

use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\UrlRewrite\Model\UrlRewrite;
use PHPUnit\Framework\TestCase;

class UrlRewriteTest extends TestCase
{
    /**
     * @var UrlRewrite
     */
    protected $model;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $context = $this->createMock(Context::class);
        $registry = $this->createMock(Registry::class);
        $resource = $this->getMockBuilder(AbstractResource::class)
            ->addMethods(['getIdFieldName'])
            ->onlyMethods(['getConnection'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $resourceCollection = $this->createMock(AbstractDb::class);
        $serializer = $this->createMock(Json::class);
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
            UrlRewrite::class,
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
