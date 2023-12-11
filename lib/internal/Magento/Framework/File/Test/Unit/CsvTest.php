<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\File\Test\Unit;

use Magento\Framework\File\Csv;
use Magento\Framework\Filesystem\Driver\File;
use PHPUnit\Framework\TestCase;

/**
 * Test class for \Magento\Framework\File\Csv.
 */
class CsvTest extends TestCase
{
    /**
     * Csv model
     *
     * @var \Magento\Framework\File\Csv
     */
    protected $_model;

    protected function setUp(): void
    {
        $this->_model = new Csv(new File());
    }

    protected function tearDown(): void
    {
        unset($this->_model);
    }

    public function testSetLineLength()
    {
        $expected = 4;
        $this->_model->setLineLength($expected);
        $lineLengthProperty = new \ReflectionProperty(Csv::class, '_lineLength');
        $lineLengthProperty->setAccessible(true);
        $actual = $lineLengthProperty->getValue($this->_model);
        $this->assertEquals($expected, $actual);
    }

    public function testSetDelimiter()
    {
        $this->assertInstanceOf(Csv::class, $this->_model->setDelimiter(','));
    }

    public function testSetEnclosure()
    {
        $this->assertInstanceOf(Csv::class, $this->_model->setEnclosure('"'));
    }

    public function testGetDataFileNonExistent()
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('File "FileNameThatShouldNotExist" does not exist');
        $file = 'FileNameThatShouldNotExist';
        $this->_model->getData($file);
    }
}
