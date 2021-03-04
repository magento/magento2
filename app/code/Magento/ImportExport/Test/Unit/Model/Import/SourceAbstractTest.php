<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportExport\Test\Unit\Model\Import;

class SourceAbstractTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\ImportExport\Model\Import\AbstractSource|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $_model = null;

    protected function setUp(): void
    {
        $this->_model = $this->getMockForAbstractClass(
            \Magento\ImportExport\Model\Import\AbstractSource::class,
            [['key1', 'key2', 'key3']]
        );
    }

    /**
     * @param array $argument
     * @dataProvider constructExceptionDataProvider
     */
    public function testConstructException($argument)
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->getMockForAbstractClass(\Magento\ImportExport\Model\Import\AbstractSource::class, [$argument]);
    }

    /**
     * @return array
     */
    public function constructExceptionDataProvider()
    {
        return ['empty column names' => [[]], 'duplicate column names' => [['1', '2', '1']]];
    }

    public function testGetColNames()
    {
        $this->assertSame(['key1', 'key2', 'key3'], $this->_model->getColNames());
    }

    /**
     */
    public function testIteratorInterface()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('wrongColumnsNumber');

        $this->assertSame(-1, $this->_model->key());
        $this->assertFalse($this->_model->valid());

        $this->_model->expects(
            $this->exactly(4)
        )->method(
            '_getNextRow'
        )->will(
            $this->onConsecutiveCalls([1, 2, 3], [4, 5, 5], [6, 7, 8], [])
        );
        $data = [];
        foreach ($this->_model as $key => $value) {
            $data[$key] = $value;
        }
        $this->assertSame(
            [
                ['key1' => 1, 'key2' => 2, 'key3' => 3],
                ['key1' => 4, 'key2' => 5, 'key3' => 5],
                ['key1' => 6, 'key2' => 7, 'key3' => 8],
            ],
            $data
        );
        $this->_model->current();
    }

    public function testSeekableInterface()
    {
        $this->assertSame(-1, $this->_model->key());
        $this->_model->seek(-1);
        $this->assertSame(-1, $this->_model->key());

        $this->_model->expects(
            $this->any()
        )->method(
            '_getNextRow'
        )->will(
            $this->onConsecutiveCalls([1, 2, 3], [4, 5, 5], [6, 7, 8], [1, 2, 3], [4, 5, 5])
        );
        $this->_model->seek(2);
        $this->assertSame(['key1' => 6, 'key2' => 7, 'key3' => 8], $this->_model->current());
        $this->_model->seek(1);
        $this->assertSame(['key1' => 4, 'key2' => 5, 'key3' => 5], $this->_model->current());
    }

    /**
     */
    public function testSeekableInterfaceException()
    {
        $this->expectException(\OutOfBoundsException::class);

        $this->_model->seek(0);
    }
}
