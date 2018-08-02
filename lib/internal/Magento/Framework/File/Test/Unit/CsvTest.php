<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\File\Test\Unit;

use Magento\Framework\Filesystem\Driver\File;

/**
 * Test class for \Magento\Framework\File\Csv.
 */
class CsvTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Csv model
     *
     * @var \Magento\Framework\File\Csv
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = new \Magento\Framework\File\Csv(new File());
    }

    protected function tearDown()
    {
        unset($this->_model);
    }

    public function testSetLineLength()
    {
        $expected = 4;
        $this->_model->setLineLength($expected);
        $lineLengthProperty = new \ReflectionProperty(\Magento\Framework\File\Csv::class, '_lineLength');
        $lineLengthProperty->setAccessible(true);
        $actual = $lineLengthProperty->getValue($this->_model);
        $this->assertEquals($expected, $actual);
    }

    public function testSetDelimiter()
    {
        $this->assertInstanceOf(\Magento\Framework\File\Csv::class, $this->_model->setDelimiter(','));
    }

    public function testSetEnclosure()
    {
        $this->assertInstanceOf(\Magento\Framework\File\Csv::class, $this->_model->setEnclosure('"'));
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage File "FileNameThatShouldNotExist" does not exist
     */
    public function testGetDataFileNonExistent()
    {
        $file = 'FileNameThatShouldNotExist';
        $this->_model->getData($file);
    }

    public function testGetDataSimpleFile()
    {
        $file = __DIR__ . '/_files/sample.csv';
        $data = $this->_model->getData($file);
        $this->assertTrue(isset($data[0][0]) && $data[0][0] == 1);
        $this->assertTrue(isset($data[0][1]) && $data[0][1] == 2);
    }

    public function testGetStreamSimpleFile()
    {
        $file = __DIR__ . '/_files/sample.csv';
        $stream = $this->_model->getStream($file);

        $data = [];
        foreach ($stream as $row) {
            $data[] = $row;
        }
        $this->assertTrue(isset($data[0][0]) && $data[0][0] == 1);
        $this->assertTrue(isset($data[0][1]) && $data[0][1] == 2);
    }
}
