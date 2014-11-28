<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Framework\DB\Logger;

class FileTest extends \PHPUnit_Framework_TestCase
{
    const DEBUG_FILE = 'debug.file.log';

    /**
     * @var \Magento\Framework\Filesystem\File\WriteInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stream;

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dir;

    /**
     * @var \Magento\Framework\DB\Logger\File
     */
    private $object;

    protected function setUp()
    {
        $this->stream = $this->getMockForAbstractClass('\Magento\Framework\Filesystem\File\WriteInterface');
        $this->dir = $this->getMockForAbstractClass('\Magento\Framework\Filesystem\Directory\WriteInterface');
        $this->dir->expects($this->any())
            ->method('openFile')
            ->with(self::DEBUG_FILE, 'a')
            ->will($this->returnValue($this->stream));
        $filesystem = $this->getMock('Magento\Framework\Filesystem', [], [], '', false);
        $filesystem->expects($this->any())
            ->method('getDirectoryWrite')
            ->will($this->returnValue($this->dir));

        $this->object = new File(
            $filesystem,
            self::DEBUG_FILE
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
     * @param $type
     *
     * @param string $q
     * @param array $bind
     * @param \Zend_Db_Statement_Pdo|null $result
     * @param string $expected
     * @dataProvider logStatsDataProvider
     */
    public function testLogStats($type, $q, array $bind, $result, $expected)
    {
        $this->stream->expects($this->once())
            ->method('write')
            ->with($this->matches($expected));
        $this->object->logStats($type, $q, $bind, $result);
    }

    /**
     * @return array
     */
    public function logStatsDataProvider()
    {
        return [
            [\Magento\Framework\DB\LoggerInterface::TYPE_CONNECT, '', [], null, '%aCONNECT%a'],
            [
                \Magento\Framework\DB\LoggerInterface::TYPE_TRANSACTION,
                'SELECT something',
                [],
                null,
                '%aTRANSACTION SELECT something%a'
            ],
            [
                \Magento\Framework\DB\LoggerInterface::TYPE_QUERY,
                'SELECT something',
                [],
                null,
                '%aSQL: SELECT something%a'
            ],
            [
                \Magento\Framework\DB\LoggerInterface::TYPE_QUERY,
                'SELECT something',
                ['data'],
                null,
                "%aQUERY%aSQL: SELECT something%aBIND: array (%a0 => 'data',%a)%a"
            ],
        ];
    }

    public function testLogStatsWithResult()
    {
        $result = $this->getMock('\Zend_Db_Statement_Pdo', [], [], '', false);
        $result->expects($this->once())
            ->method('rowCount')
            ->will($this->returnValue(10));
        $this->stream->expects($this->once())
            ->method('write')
            ->with($this->logicalNot($this->matches('%aSQL: SELECT something%aAFF: 10')));

        $this->object->logStats(
            \Magento\Framework\DB\LoggerInterface::TYPE_QUERY,
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

    public function testLogException()
    {
        $exception = new \Exception('error message');
        $expected = "%aEXCEPTION%a'Exception'%a'error message'%a";

        $this->stream->expects($this->once())
            ->method('write')
            ->with($this->matches($expected));

        $this->object->logException($exception);
    }
}
