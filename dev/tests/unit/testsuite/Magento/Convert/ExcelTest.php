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
 * @category    Magento
 * @package     unit_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Magento_Convert Test Case for \Magento\Convert\Excel Export
 */
namespace Magento\Convert;

class ExcelTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test data
     *
     * @var array
     */
    private $_testData = array(
        array('ID', 'Name', 'Email', 'Group', 'Telephone', 'ZIP', 'Country', 'State/Province'),
        array(1, 'Jon Doe', 'jon.doe@magento.com', 'General', '310-111-1111', 90232, 'United States', 'California')
    );

    /**
     * Path for Sample File
     *
     * @return string
     */
    protected function _getSampleOutputFile()
    {
        return __DIR__ . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'output.txt';
    }

    /**
     * Callback method
     *
     * @param array $row
     * @return array
     */
    public function callbackMethod($row)
    {
        $data = array();
        foreach ($row as $value) {
            $data[] =  $value.'_TRUE_';
        }
        return $data;
    }

    /**
     * Test \Magento\Convert\Excel->convert()
     * \Magento\Convert\Excel($iterator)
     *
     * @return void
     */
    public function testConvert()
    {
        $convert = new \Magento\Convert\Excel(new \ArrayIterator($this->_testData));
        $isEqual = (file_get_contents($this->_getSampleOutputFile()) == $convert->convert());
        $this->assertTrue($isEqual, 'Failed asserting that data is the same.');
    }

    /**
     * Test \Magento\Convert\Excel->convert()
     * \Magento\Convert\Excel($iterator, $callbackMethod)
     *
     * @return void
     */
    public function testConvertCallback()
    {
        $convert = new \Magento\Convert\Excel(new \ArrayIterator($this->_testData), array($this, 'callbackMethod'));
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
        $adapter = new \Magento\Filesystem\Adapter\Local();
        $filesystem = new \Magento\Filesystem($adapter);

        $name = md5(microtime());
        $file = TESTS_TEMP_DIR . DIRECTORY_SEPARATOR . $name . '.xml';

        $stream = $filesystem->createAndOpenStream($file, 'w+', TESTS_TEMP_DIR);
        $stream->lock(true);

        if (!$callback) {
            $convert = new \Magento\Convert\Excel(new \ArrayIterator($this->_testData));
        } else {
            $convert = new \Magento\Convert\Excel(new \ArrayIterator($this->_testData), array($this, 'callbackMethod'));
        }

        $convert->write($stream);
        $stream->unlock();
        $stream->close();

        return $file;
    }

    /**
     * Test \Magento\Convert\Excel->write()
     * \Magento\Convert\Excel($iterator)
     *
     * @return void
     */
    public function testWrite()
    {
        $file = $this->_writeFile();
        $isEqual = (file_get_contents($file) == file_get_contents($this->_getSampleOutputFile()));
        $this->assertTrue($isEqual, 'Failed asserting that data from files is the same.');
    }

    /**
     * Test \Magento\Convert\Excel->write()
     * \Magento\Convert\Excel($iterator, $callbackMethod)
     *
     * @return void
     */
    public function testWriteCallback()
    {
        $file = $this->_writeFile(true);
        $this->assertContains('_TRUE_', file_get_contents($file), 'Failed asserting that callback method is called.');
    }
}
