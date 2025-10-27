<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\DB\Test\Unit\Helper\Mysql;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Helper\Mysql\Fulltext;
use Magento\Framework\DB\Select;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FulltextTest extends TestCase
{
    /**
     * @return void
     */
    public function testGetMatchQuery(): void
    {
        /** @var Fulltext $select */
        $select = (new ObjectManager($this))->getObject(
            Fulltext::class,
            ['resource' => $this->getResourceMock()]
        );

        $result = $select->getMatchQuery(
            ['title', 'description'],
            'some searchable text',
            Fulltext::FULLTEXT_MODE_NATURAL
        );
        $expectedResult = "MATCH (title, description) AGAINST ('some searchable text' IN NATURAL LANGUAGE MODE)";

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @param $isCondition
     *
     * @return void
     * @dataProvider matchProvider
     */
    public function testMatch($isCondition): void
    {
        $fullCondition = "MATCH (title, description) AGAINST ('some searchable text' IN NATURAL LANGUAGE MODE)";

        $resource = $this->getResourceMock();

        /** @var Select|MockObject $select */
        $select = $this->getMockBuilder(Select::class)
            ->disableOriginalConstructor()
            ->getMock();
        $select->expects($this->once())
            ->method($isCondition ? 'where' : 'orWhere')
            ->with($fullCondition);

        /** @var Fulltext $fulltext */
        $fulltext = (new ObjectManager($this))->getObject(
            Fulltext::class,
            ['resource' => $resource]
        );

        $result = $fulltext->match(
            $select,
            ['title', 'description'],
            'some searchable text',
            $isCondition,
            Fulltext::FULLTEXT_MODE_NATURAL
        );

        $this->assertEquals($select, $result);
    }

    /**
     * @return array
     */
    public static function matchProvider(): array
    {
        return [[true], [false]];
    }

    /**
     * @return MockObject
     */
    protected function getResourceMock(): MockObject
    {
        $connection = $this->getMockBuilder(AdapterInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $connection
            ->method('quote')
            ->with('some searchable text')
            ->willReturn("'some searchable text'");

        $resource = $this->getMockBuilder(ResourceConnection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $resource->expects($this->any())
            ->method('getConnection')
            ->willReturn($connection);

        return $resource;
    }
}
