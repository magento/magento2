<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test class for \Magento\Framework\File\Csv.
 */
namespace Magento\Framework\File;

class CsvTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Csv model
     *
     * @var \Magento\Framework\File\Csv
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = new \Magento\Framework\File\Csv();
    }

    protected function tearDown()
    {
        unset($this->_model);
    }

    public function testSetLineLength()
    {
        $expected = 4;
        $this->_model->setLineLength($expected);
        $lineLengthProperty = new \ReflectionProperty(
            'Magento\Framework\File\Csv', '_lineLength'
        );
        $lineLengthProperty->setAccessible(true);
        $actual = $lineLengthProperty->getValue($this->_model);
        $this->assertEquals($expected, $actual);
    }

    public function testSetDelimiter()
    {
        $this->assertInstanceOf('\Magento\Framework\File\Csv', $this->_model->setDelimiter(','));
    }

    public function testSetEnclosure()
    {
        $this->assertInstanceOf('\Magento\Framework\File\Csv', $this->_model->setEnclosure('"'));
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage File "FileNameThatShouldNotExist" do not exists
     */
    public function testGetDataFileNonExistent()
    {
        $file = 'FileNameThatShouldNotExist';
        $this->_model->getData($file);
    }
}
