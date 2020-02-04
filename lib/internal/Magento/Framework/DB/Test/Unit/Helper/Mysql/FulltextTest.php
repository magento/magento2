<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DB\Test\Unit\Helper\Mysql;

use Magento\Framework\DB\Helper\Mysql\Fulltext;
use Magento\Framework\DB\Select;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class FulltextTest extends \PHPUnit\Framework\TestCase
{
    public function testGetMatchQuery()
    {
        /** @var Fulltext $select */
        $select = (new ObjectManager($this))->getObject(
            \Magento\Framework\DB\Helper\Mysql\Fulltext::class,
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
     * @dataProvider matchProvider
     */
    public function testMatch($isCondition)
    {
        $fullCondition = "MATCH (title, description) AGAINST ('some searchable text' IN NATURAL LANGUAGE MODE)";

        $resource = $this->getResourceMock();

        /** @var \Magento\Framework\DB\Select|\PHPUnit_Framework_MockObject_MockObject $select */
        $select = $this->getMockBuilder(\Magento\Framework\DB\Select::class)
            ->disableOriginalConstructor()
            ->getMock();
        $select->expects($this->once())
            ->method($isCondition ? 'where' : 'orWhere')
            ->with($fullCondition);

        /** @var Fulltext $fulltext */
        $fulltext = (new ObjectManager($this))->getObject(
            \Magento\Framework\DB\Helper\Mysql\Fulltext::class,
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
    public function matchProvider()
    {
        return [[true], [false]];
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getResourceMock()
    {
        $connection = $this->getMockBuilder(\Magento\Framework\DB\Adapter\AdapterInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $connection->expects($this->at(0))
            ->method('quote')
            ->with($this->equalTo('some searchable text'))
            ->will($this->returnValue("'some searchable text'"));

        $resource = $this->getMockBuilder(\Magento\Framework\App\ResourceConnection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $resource->expects($this->any())
            ->method('getConnection')
            ->willReturn($connection);

        return $resource;
    }
}
