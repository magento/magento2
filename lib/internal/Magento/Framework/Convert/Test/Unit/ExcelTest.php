<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Magento_Convert Test Case for \Magento\Framework\Convert\Excel Export
 */
namespace Magento\Framework\Convert\Test\Unit;

class ExcelTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test data
     *
     * @var array
     */
    private $_testData = [
        [
            'ID', 'Name', 'Email', 'Group', 'Telephone', '+Telephone', 'ZIP', '0ZIP', 'Country', 'State/Province',
            'Symbol=', 'Symbol-', 'Symbol+'
        ],
        [
            1, 'Jon Doe', 'jon.doe@magento.com', 'General', '310-111-1111', '+310-111-1111', 90232, '090232',
            'United States', 'California', '=', '-', '+'
        ],
    ];

    protected $_testHeader = [
        'HeaderID', 'HeaderName', 'HeaderEmail', 'HeaderGroup', 'HeaderPhone', 'Header+Phone', 'HeaderZIP',
        'Header0ZIP', 'HeaderCountry', 'HeaderRegion', 'HeaderSymbol=', 'HeaderSymbol-', 'HeaderSymbol+'
    ];

    protected $_testFooter = [
        'FooterID', 'FooterName', 'FooterEmail', 'FooterGroup', 'FooterPhone', 'Footer+Phone', 'FooterZIP',
        'Footer0ZIP', 'FooterCountry', 'FooterRegion', 'FooterSymbol=', 'FooterSymbol-', 'FooterSymbol+'
    ];

    /**
     * Path for Sample File
     *
     * @return string
     */
    protected function _getSampleOutputFile()
    {
        return __DIR__ . '/_files/sample.xml';
    }

    /**
     * Callback method
     *
     * @param array $row
     * @return array
     */
    public function callbackMethod($row)
    {
        $data = [];
        foreach ($row as $value) {
            $data[] = $value . '_TRUE_';
        }
        return $data;
    }

    /**
     * Test \Magento\Framework\Convert\Excel->convert()
     * \Magento\Framework\Convert\Excel($iterator)
     *
     * @return void
     */
    public function testConvert()
    {
        $convert = new \Magento\Framework\Convert\Excel(new \ArrayIterator($this->_testData));
        $convert->setDataHeader($this->_testHeader);
        $convert->setDataFooter($this->_testFooter);
        $this->assertXmlStringEqualsXmlString(
            file_get_contents($this->_getSampleOutputFile()),
            $convert->convert()
        );
    }

    /**
     * Test \Magento\Framework\Convert\Excel->convert()
     * \Magento\Framework\Convert\Excel($iterator, $callbackMethod)
     *
     * @return void
     */
    public function testConvertCallback()
    {
        $convert = new \Magento\Framework\Convert\Excel(
            new \ArrayIterator($this->_testData),
            [$this, 'callbackMethod']
        );
        $this->assertContains('_TRUE_', $convert->convert(), 'Failed asserting that callback method is called.');
    }

    /**
     * Write Data into File
     *
     * @param bool $callback
     * @return string
     */
    protected function _writeFile($callback = false)
    {
        $name = md5(microtime());
        $file = TESTS_TEMP_DIR . '/' . $name . '.xml';

        $stream = new \Magento\Framework\Filesystem\File\Write(
            $file,
            new \Magento\Framework\Filesystem\Driver\File(),
            'w+'
        );
        $stream->lock();

        if (!$callback) {
            $convert = new \Magento\Framework\Convert\Excel(new \ArrayIterator($this->_testData));
            $convert->setDataHeader($this->_testHeader);
            $convert->setDataFooter($this->_testFooter);
        } else {
            $convert = new \Magento\Framework\Convert\Excel(
                new \ArrayIterator($this->_testData),
                [$this, 'callbackMethod']
            );
        }

        $convert->write($stream);
        $stream->unlock();
        $stream->close();

        return $file;
    }

    /**
     * Test \Magento\Framework\Convert\Excel->write()
     * \Magento\Framework\Convert\Excel($iterator)
     *
     * @return void
     */
    public function testWrite()
    {
        $file = $this->_writeFile();
        $this->assertXmlStringEqualsXmlString(
            file_get_contents($this->_getSampleOutputFile()),
            file_get_contents($file)
        );
    }

    /**
     * Test \Magento\Framework\Convert\Excel->write()
     * \Magento\Framework\Convert\Excel($iterator, $callbackMethod)
     *
     * @return void
     */
    public function testWriteCallback()
    {
        $file = $this->_writeFile(true);
        $this->assertContains('_TRUE_', file_get_contents($file), 'Failed asserting that callback method is called.');
    }
}
