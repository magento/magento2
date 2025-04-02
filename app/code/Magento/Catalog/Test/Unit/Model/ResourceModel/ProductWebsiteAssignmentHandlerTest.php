<?php
/**
 * Copyright 2024 Adobe
 * All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);
namespace Magento\Catalog\Test\Unit\Model\ResourceModel;

use Magento\Catalog\Model\ResourceModel\Product\Website\Link;
use Magento\Catalog\Model\ResourceModel\ProductWebsiteAssignmentHandler;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ProductWebsiteAssignmentHandlerTest extends TestCase
{
    /**
     * @var ProductWebsiteAssignmentHandler
     */
    protected $handler;

    /**
     * @var Link|MockObject
     */
    protected $productLinkMock;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->productLinkMock = $this->createPartialMock(
            Link::class,
            ['updateProductWebsite']
        );
        $this->handler = $objectManager->getObject(
            ProductWebsiteAssignmentHandler::class,
            [
                'productLink' => $this->productLinkMock
            ]
        );
    }

    /**
     * @param $actualData
     * @param $expectedResult
     * @dataProvider productWebsitesDataProvider
     * @throws \Exception
     */
    public function testUpdateProductWebsiteReturnValidResult($actualData, $expectedResult)
    {
        $this->productLinkMock->expects($this->any())->method('updateProductWebsite')->willReturn($expectedResult);
        $this->assertEquals(
            $actualData['entityData'],
            $this->handler->execute($actualData['entityType'], $actualData['entityData'])
        );
    }

    /**
     * @return array
     */
    public static function productWebsitesDataProvider(): array
    {
        return [
            [
                [
                    'entityType' => 'product',
                    'entityData' => [
                        'entity_id' => '12345',
                        'website_ids' => ['1', '2', '3'],
                        'name' => 'test-1',
                        'sku' => 'test-1'
                    ]
                ],
                true
            ],
            [
                [
                    'entityType' => 'product',
                    'entityData' => [
                        'entity_id' => null,
                        'website_ids' => ['1', '2', '3'],
                        'name' => 'test-1',
                        'sku' => 'test-1'
                    ]
                ],
                false
            ],
            [
                [
                    'entityType' => 'product',
                    'entityData' => [
                        'entity_id' => '12345',
                        'website_ids' => [null],
                        'name' => 'test-1',
                        'sku' => 'test-1'
                    ]
                ],
                false
            ]
        ];
    }
}
