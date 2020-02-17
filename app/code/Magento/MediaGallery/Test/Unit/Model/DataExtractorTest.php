<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGallery\Test\Unit\Model;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\MediaGallery\Model\Asset;
use Magento\MediaGallery\Model\Keyword;
use Magento\MediaGalleryApi\Api\Data\AssetInterface;
use Magento\MediaGalleryApi\Api\Data\KeywordInterface;
use Magento\MediaGallery\Model\DataExtractor;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DataExtractorTest extends TestCase
{
    /**
     * @var DataExtractor|MockObject
     */
    private $dataExtractor;

    /**
     * Initialize basic test class mocks
     */
    protected function setUp(): void
    {
        $this->dataExtractor = new DataExtractor();
    }

    /**
     * Test extract object data by interface
     *
     * @dataProvider assetProvider
     *
     * @param string $class
     * @param string|null $interfaceClass
     * @param array $expectedData
     *
     * @throws \ReflectionException
     */
    public function testExtractData(string $class, $interfaceClass, array $expectedData): void
    {
        $data = [];
        foreach ($expectedData as $expectedDataKey => $expectedDataItem) {
            $data[$expectedDataKey] = $expectedDataItem['value'];
        }
        $model = (new ObjectManager($this))->getObject(
            $class,
            [
                'data' => $data,
            ]
        );
        $receivedData = $this->dataExtractor->extract($model, $interfaceClass);
        $this->checkValues($expectedData, $receivedData, $model);
    }

    /**
     * @param array $expectedData
     * @param array $data
     * @param object $model
     */
    protected function checkValues(array $expectedData, array $data, $model)
    {
        foreach ($expectedData as $expectedDataKey => $expectedDataItem) {
            $this->assertEquals($data[$expectedDataKey] ?? null, $model->{$expectedDataItem['method']}());
            $this->assertEquals($data[$expectedDataKey] ?? null, $expectedDataItem['value']);
        }
        $this->assertEquals(array_keys($expectedData), array_keys($expectedData));
    }

    /**
     * @return array
     */
    public function assetProvider()
    {
        return [
            'Asset conversion with interface' => [
                Asset::class,
                AssetInterface::class,
                [
                    'id' => [
                        'value' => 2,
                        'method' => 'getId',
                    ],
                    'path' => [
                        'value' => 'path',
                        'method' => 'getPath',
                    ],
                    'title' => [
                        'value' => 'title',
                        'method' => 'getTitle',
                    ],
                    'source' => [
                        'value' => 'source',
                        'method' => 'getSource',
                    ],
                    'content_type' => [
                        'value' => 'content_type',
                        'method' => 'getContentType',
                    ],
                    'width' => [
                        'value' => 3,
                        'method' => 'getWidth',
                    ],
                    'height' => [
                        'value' => 4,
                        'method' => 'getHeight',
                    ],
                    'created_at' => [
                        'value' => '2019-11-28 10:40:09',
                        'method' => 'getCreatedAt',
                    ],
                    'updated_at' => [
                        'value' => '2019-11-28 10:41:08',
                        'method' => 'getUpdatedAt',
                    ],
                ],
            ],
            'Keyword conversion without interface' => [
                Keyword::class,
                null,
                [
                    'id' => [
                        'value' => 2,
                        'method' => 'getId',
                    ],
                    'keyword' => [
                        'value' => 'keyword',
                        'method' => 'getKeyword',
                    ],
                ],
            ]
        ];
    }
}
