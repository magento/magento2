<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\DB\Test\Unit\Logger;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Logger\File;
use Magento\Framework\DB\Logger\QueryAnalyzerInterface;
use Magento\Framework\DB\LoggerInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\File\WriteInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FileTest extends TestCase
{
    public const DEBUG_FILE = 'debug.file.log';

    /**
     * @var WriteInterface|MockObject
     */
    private $stream;

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface|MockObject
     */
    private $dir;

    /**
     * @var File
     */
    private $object;

    /**
     * @var ResourceConnection|MockObject
     */
    private QueryAnalyzerInterface $queryAnalyzer;

    /**
     * @var Filesystem|MockObject
     */
    private Filesystem $filesystem;

    protected function setUp(): void
    {
        $this->stream = $this->getMockForAbstractClass(WriteInterface::class);
        $this->dir = $this->getMockForAbstractClass(\Magento\Framework\Filesystem\Directory\WriteInterface::class);
        $this->dir->expects($this->any())
            ->method('openFile')
            ->with(self::DEBUG_FILE, 'a')
            ->willReturn($this->stream);
        $this->filesystem = $this->createMock(Filesystem::class);
        $this->filesystem->expects($this->any())
            ->method('getDirectoryWrite')
            ->willReturn($this->dir);
        $this->queryAnalyzer = $this->createMock(QueryAnalyzerInterface::class);

        $this->object = new File(
            $this->filesystem,
            self::DEBUG_FILE,
            false,
            0.05,
            false,
            true,
            $this->queryAnalyzer,
        );
    }

    public function testLog()
    {
        $input = 'message';
        $expected = '%amessage';

        $this->stream->expects($this->once())
            ->method('write')
            ->with($this->matches($expected));

        $this->object->log($input);
    }

    /**
     * @return void
     */
    public function testNothingToLog(): void
    {
        $this->stream->expects($this->never())->method('write');
        $this->object->logStats(
            LoggerInterface::TYPE_QUERY,
            'EXPLAIN SELECT something',
            ['data']
        );
    }

    /**
     * @param string $type
     * @param string $q
     * @param array $bind
     * @param string $expected
     * @dataProvider logStatsDataProvider
     */
    public function testLogStats(string $type, string $q, array $bind, string $expected)
    {
        $this->stream->expects($this->once())
            ->method('write')
            ->with($this->matches($expected));
        $this->object->logStats($type, $q, $bind);
    }

    /**
     * @return array
     */
    public static function logStatsDataProvider()
    {
        return [
            [LoggerInterface::TYPE_CONNECT, '', [], '%aCONNECT%a'],
            [
                LoggerInterface::TYPE_TRANSACTION,
                'SELECT something',
                [],
                '%aTRANSACTION SELECT something%a'
            ],
            [
                LoggerInterface::TYPE_QUERY,
                'SELECT something',
                [],
                '%aSQL: SELECT something%a'
            ],
            [
                LoggerInterface::TYPE_QUERY,
                'SELECT something',
                ['data'],
                "%aQUERY%aSQL: SELECT something%aBIND: array (%a0 => 'data',%a)%a"
            ]
        ];
    }

    public function testLogStatsWithResult()
    {
        $result = $this->createMock(\Zend_Db_Statement_Pdo::class);
        $result->expects($this->once())
            ->method('rowCount')
            ->willReturn(10);
        $this->stream->expects($this->once())
            ->method('write')
            ->with($this->logicalNot($this->matches('%aSQL: SELECT something%aAFF: 10')));

        $this->object->logStats(
            LoggerInterface::TYPE_QUERY,
            'SELECT something',
            [],
            $result
        );
    }

    public function testLogStatsUnknownType()
    {
        $this->stream->expects($this->once())
            ->method('write')
            ->with($this->logicalNot($this->matches('%aSELECT something%a')));
        $this->object->logStats('unknown', 'SELECT something');
    }

    public function testcritical()
    {
        $exception = new \Exception('error message');
        $expected = "%aEXCEPTION%aException%aerror message%a";

        $this->stream->expects($this->once())
            ->method('write')
            ->with($this->matches($expected));

        $this->object->critical($exception);
    }
}
