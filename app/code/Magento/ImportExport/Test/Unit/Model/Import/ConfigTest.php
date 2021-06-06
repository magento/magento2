<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ImportExport\Test\Unit\Model\Import;

use Magento\Framework\Config\CacheInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\ImportExport\Model\Import\Config;
use Magento\ImportExport\Model\Import\Config\Reader;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    /**
     * @var Reader|MockObject
     */
    protected $readerMock;

    /**
     * @var CacheInterface|MockObject
     */
    protected $cacheMock;

    /**
     * @var SerializerInterface
     */
    private $serializerMock;

    /**
     * @var string
     */
    protected $cacheId = 'some_id';

    /**
     * @var Config
     */
    protected $model;

    protected function setUp(): void
    {
        $this->readerMock = $this->createMock(Reader::class);
        $this->cacheMock = $this->getMockForAbstractClass(CacheInterface::class);
        $this->serializerMock = $this->getMockForAbstractClass(SerializerInterface::class);
    }

    /**
     * @param array $value
     * @param null|string $expected
     * @dataProvider getEntitiesDataProvider
     */
    public function testGetEntities($value, $expected)
    {
        $this->cacheMock->expects(
            $this->any()
        )->method(
            'load'
        )->with(
            $this->cacheId
        )->willReturn(
            false
        );
        $this->readerMock->expects($this->any())->method('read')->willReturn($value);
        $this->model = new Config(
            $this->readerMock,
            $this->cacheMock,
            $this->cacheId,
            $this->serializerMock
        );
        $this->assertEquals($expected, $this->model->getEntities('entities'));
    }

    /**
     * @return array
     */
    public function getEntitiesDataProvider()
    {
        return [
            'entities_key_exist' => [['entities' => 'value'], 'value'],
            'return_default_value' => [['key_one' => 'value'], null]
        ];
    }

    /**
     * @param array $configData
     * @param string $entity
     * @param string[] $expectedResult
     * @dataProvider getEntityTypesDataProvider
     */
    public function testGetEntityTypes($configData, $entity, $expectedResult)
    {
        $this->cacheMock->expects(
            $this->any()
        )->method(
            'load'
        )->with(
            $this->cacheId
        )->willReturn(
            false
        );
        $this->readerMock->expects($this->any())->method('read')->willReturn($configData);
        $this->model = new Config(
            $this->readerMock,
            $this->cacheMock,
            $this->cacheId,
            $this->serializerMock
        );
        $this->assertEquals($expectedResult, $this->model->getEntityTypes($entity));
    }

    /**
     * @return array
     */
    public function getEntityTypesDataProvider()
    {
        return [
            'valid type' => [
                [
                    'entities' => [
                        'catalog_product' => [
                            'types' => ['configurable', 'simple'],
                        ],
                    ],
                ],
                'catalog_product',
                ['configurable', 'simple'],
            ],
            'not existing entity' => [
                [
                    'entities' => [
                        'catalog_product' => [
                            'types' => ['configurable', 'simple'],
                        ],
                    ],
                ],
                'not existing entity',
                [],
            ],
        ];
    }
}
