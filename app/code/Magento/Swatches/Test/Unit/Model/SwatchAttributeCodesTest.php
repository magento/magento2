<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Swatches\Test\Unit\Model;

use Magento\Eav\Model\Entity\Attribute;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\Pdo\Mysql;
use Magento\Framework\DB\Select;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Swatches\Model\SwatchAttributeCodes;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SwatchAttributeCodesTest extends TestCase
{
    const ATTRIBUTE_TABLE = 'eav_attribute';
    const ATTRIBUTE_OPTION_TABLE = 'eav_attribute_option';
    const SWATCH_OPTION_TABLE = 'eav_attribute_option_swatch';
    const CACHE_KEY = 'swatch-attribute-list';

    /**
     * @var SwatchAttributeCodes
     */
    private $swatchAttributeCodesModel;

    /**
     * @var CacheInterface|MockObject
     */
    private $cache;

    /**
     * @var ResourceConnection|MockObject
     */
    private $resourceConnection;

    /**
     * @var array
     */
    private $swatchAttributesCodes = [
        10 => 'text_swatch',
        11 => 'image_swatch',
    ];

    protected function setUp(): void
    {
        $this->cache = $this->createPartialMock(CacheInterface::class, [
            'getFrontend',
            'load',
            'save',
            'remove',
            'clean'
        ]);

        $this->resourceConnection = $this->createPartialMock(
            ResourceConnection::class,
            ['getConnection', 'getTableName']
        );

        $this->swatchAttributeCodesModel = (new ObjectManager($this))->getObject(SwatchAttributeCodes::class, [
            'cache' => $this->cache,
            'resourceConnection' => $this->resourceConnection,
            'cacheKey' => self::CACHE_KEY,
            'cacheTags' => [Attribute::CACHE_TAG],
        ]);
    }

    /**
     * @dataProvider dataForGettingCodes
     * @param array|bool $swatchAttributeCodesCache
     * @param array $expected
     */
    public function testGetCodes($swatchAttributeCodesCache, $expected)
    {
        $this->cache
            ->method('load')
            ->with(self::CACHE_KEY)
            ->willReturn($swatchAttributeCodesCache);

        $adapterMock = $this->createPartialMock(Mysql::class, ['select', 'fetchPairs']);

        $selectMock = $this->createPartialMock(Select::class, ['from', 'where', 'join']);
        $selectMock
            ->method('from')
            ->withConsecutive(
                [
                    self::identicalTo(
                        ['a' => self::ATTRIBUTE_TABLE]
                    )
                ],
                [
                    self::identicalTo(
                        ['o' => self::ATTRIBUTE_OPTION_TABLE]
                    )
                ]
            )
            ->willReturnSelf();

        // used anything for second argument because of new \Zend_Db_Expt used in code.
        $selectMock->method('where')
            ->with(
                self::identicalTo('a.attribute_id IN (?)'),
                self::anything()
            )
            ->willReturnSelf();

        $adapterMock->method('select')
            ->willReturn($selectMock);
        $adapterMock->method('fetchPairs')
            ->with($selectMock)
            ->willReturn($this->swatchAttributesCodes);

        $this->resourceConnection
            ->method('getConnection')
            ->willReturn($adapterMock);

        $this->resourceConnection
            ->method('getTableName')
            ->withConsecutive(
                [self::ATTRIBUTE_TABLE],
                [self::ATTRIBUTE_OPTION_TABLE],
                [self::SWATCH_OPTION_TABLE]
            )->will(self::onConsecutiveCalls(
                self::ATTRIBUTE_TABLE,
                self::ATTRIBUTE_OPTION_TABLE,
                self::SWATCH_OPTION_TABLE
            ));

        $result = $this->swatchAttributeCodesModel->getCodes();
        $this->assertEquals($expected, $result);
    }

    /**
     * @return array
     */
    public function dataForGettingCodes()
    {
        return [
            [false, $this->swatchAttributesCodes],
            [json_encode($this->swatchAttributesCodes), $this->swatchAttributesCodes]
        ];
    }
}
