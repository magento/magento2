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
 * @package     Magento_Profiler
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test case for Magento_Profiler_Output_Csvfile
 *
 * @group profiler
 */
class Magento_Profiler_Output_CsvfileTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Magento_Profiler_Output_Csvfile
     */
    protected $_object;

    /**
     * @var string
     */
    protected $_actualCsvFile;

    public static function setUpBeforeClass()
    {
        Magento_Profiler::enable();
        /* Profiler measurements fixture */
        $timersProperty = new ReflectionProperty('Magento_Profiler', '_timers');
        $timersProperty->setAccessible(true);
        $timersProperty->setValue(include __DIR__ . '/../_files/timers.php');
        $timersProperty->setAccessible(false);
    }

    public static function tearDownAfterClass()
    {
        Magento_Profiler::reset();
    }

    protected function setUp()
    {
        do {
            $this->_actualCsvFile = TESTS_TEMP_DIR . DIRECTORY_SEPARATOR . md5(time() + microtime(true));
        } while (file_exists($this->_actualCsvFile));
    }

    /**
     * @dataProvider displayDataProvider
     */
    public function testDisplay($delimiter, $enclosure, $expectedCsvFile)
    {
        $this->_object = new Magento_Profiler_Output_Csvfile($this->_actualCsvFile, null, $delimiter, $enclosure);
        $this->_object->display();

        $this->assertFileEquals($expectedCsvFile, $this->_actualCsvFile);
    }

    public function displayDataProvider()
    {
        return array(
            'default delimiter & enclosure' => array(',', '"', __DIR__ . '/../_files/output_default.csv'),
            'custom delimiter & enclosure'  => array('.', '`', __DIR__ . '/../_files/output_custom.csv'),
        );
    }

    public function testDisplayDefaults()
    {
        $this->_object = new Magento_Profiler_Output_Csvfile($this->_actualCsvFile);
        $this->_object->display();

        $expectedCsvFile = __DIR__ . '/../_files/output_default.csv';
        $this->assertFileEquals($expectedCsvFile, $this->_actualCsvFile);
    }
}
