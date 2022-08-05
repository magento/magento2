<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\UrlRewrite\Test\Unit\Service\V1\Data;

use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite as UrlRewriteService;
use PHPUnit\Framework\TestCase;

class UrlRewriteTest extends TestCase
{
    /**
     * @var \Magento\UrlRewrite\Model\UrlRewrite
     */
    protected $model;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

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
            UrlRewriteService::class,
            [
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
