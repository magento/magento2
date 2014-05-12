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

/**
 * Test class for \Magento\TestFramework\Bootstrap\Settings.
 */
namespace Magento\Test\Bootstrap;

class SettingsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\TestFramework\Bootstrap\Settings
     */
    protected $_object;

    /**
     * @var string
     */
    protected $_fixtureDir;

    /**
     * Define the fixture directory to be used in both data providers and tests
     *
     * @param string|null $name
     * @param array $data
     * @param string $dataName
     */
    public function __construct($name = null, array $data = array(), $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->_fixtureDir = realpath(__DIR__ . '/_files') . '/';
    }

    protected function setUp()
    {
        $this->_object = new \Magento\TestFramework\Bootstrap\Settings(
            $this->_fixtureDir,
            array(
                'item_label' => 'Item Label',
                'number_of_items' => 42,
                'item_price' => 12.99,
                'is_in_stock' => true,
                'free_shipping' => 'enabled',
                'zero_value' => '0',
                'test_file' => 'metrics.php',
                'all_xml_files' => '*.xml',
                'all_xml_or_one_php_file' => '{*.xml,4.php}',
                'one_xml_or_any_php_file' => '1.xml;?.php',
                'config_file_with_dist' => '1.xml',
                'config_file_no_dist' => '2.xml',
                'no_config_file_dist' => '3.xml'
            )
        );
    }

    protected function tearDown()
    {
        $this->_object = null;
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Base path 'non_existing_dir' has to be an existing directory.
     */
    public function testConstructorNonExistingBaseDir()
    {
        new \Magento\TestFramework\Bootstrap\Settings('non_existing_dir', array());
    }

    /**
     * @param string $settingName
     * @param mixed $defaultValue
     * @param mixed $expectedResult
     * @dataProvider getDataProvider
     */
    public function testGet($settingName, $defaultValue, $expectedResult)
    {
        $this->assertSame($expectedResult, $this->_object->get($settingName, $defaultValue));
    }

    public function getDataProvider()
    {
        return array(
            'string type' => array('item_label', null, 'Item Label'),
            'integer type' => array('number_of_items', null, 42),
            'float type' => array('item_price', null, 12.99),
            'boolean type' => array('is_in_stock', null, true),
            'non-existing' => array('non_existing', null, null),
            'zero string' => array('zero_value', '1', '0'),
            'default value' => array('non_existing', 'default', 'default')
        );
    }

    /**
     * @param string $settingName
     * @param bool $expectedResult
     * @dataProvider getAsBooleanDataProvider
     */
    public function testGetAsBoolean($settingName, $expectedResult)
    {
        $this->assertSame($expectedResult, $this->_object->getAsBoolean($settingName));
    }

    public function getAsBooleanDataProvider()
    {
        return array(
            'non-enabled string' => array('item_label', false),
            'non-enabled boolean' => array('is_in_stock', false),
            'enabled string' => array('free_shipping', true)
        );
    }

    /**
     * @param string $settingName
     * @param mixed $defaultValue
     * @param string $expectedResult
     * @dataProvider getAsFileDataProvider
     */
    public function testGetAsFile($settingName, $defaultValue, $expectedResult)
    {
        $this->assertSame($expectedResult, $this->_object->getAsFile($settingName, $defaultValue));
    }

    public function getAsFileDataProvider()
    {
        return array(
            'existing file' => array('test_file', '', "{$this->_fixtureDir}metrics.php"),
            'zero value setting' => array('zero_value', 'default_should_be_ignored', "{$this->_fixtureDir}0"),
            'empty default value' => array('non_existing_file', '', ''),
            'zero default value' => array('non_existing_file', '0', "{$this->_fixtureDir}0"),
            'default value' => array('non_existing_file', 'metrics.php', "{$this->_fixtureDir}metrics.php")
        );
    }

    /**
     * @param string $settingName
     * @param string $expectedResult
     * @dataProvider getAsMatchingPathsDataProvider
     */
    public function testGetAsMatchingPaths($settingName, $expectedResult)
    {
        $actualResult = $this->_object->getAsMatchingPaths($settingName);
        if (is_array($actualResult)) {
            sort($actualResult);
        }
        $this->assertEquals($expectedResult, $actualResult);
    }

    public function getAsMatchingPathsDataProvider()
    {
        return array(
            'single pattern' => array(
                'all_xml_files',
                array("{$this->_fixtureDir}1.xml", "{$this->_fixtureDir}2.xml")
            ),
            'pattern with braces' => array(
                'all_xml_or_one_php_file',
                array("{$this->_fixtureDir}1.xml", "{$this->_fixtureDir}2.xml", "{$this->_fixtureDir}4.php")
            ),
            'multiple patterns' => array(
                'one_xml_or_any_php_file',
                array("{$this->_fixtureDir}1.xml", "{$this->_fixtureDir}4.php")
            ),
            'non-existing setting' => array('non_existing', array()),
            'setting with zero value' => array('zero_value', array("{$this->_fixtureDir}0"))
        );
    }

    /**
     * @param string $settingName
     * @param mixed $expectedResult
     * @dataProvider getAsConfigFileDataProvider
     */
    public function testGetAsConfigFile($settingName, $expectedResult)
    {
        $actualResult = $this->_object->getAsConfigFile($settingName);
        if (is_array($actualResult)) {
            sort($actualResult);
        }
        $this->assertEquals($expectedResult, $actualResult);
    }

    public function getAsConfigFileDataProvider()
    {
        return array(
            'config file & dist file' => array('config_file_with_dist', "{$this->_fixtureDir}1.xml"),
            'config file & no dist file' => array('config_file_no_dist', "{$this->_fixtureDir}2.xml"),
            'no config file & dist file' => array('no_config_file_dist', "{$this->_fixtureDir}3.xml.dist")
        );
    }

    /**
     * @param string $settingName
     * @param string $expectedExceptionMsg
     * @dataProvider getAsConfigFileExceptionDataProvider
     */
    public function testGetAsConfigFileException($settingName, $expectedExceptionMsg)
    {
        $this->setExpectedException('Magento\Framework\Exception', $expectedExceptionMsg);
        $this->_object->getAsConfigFile($settingName);
    }

    public function getAsConfigFileExceptionDataProvider()
    {
        return array(
            'non-existing setting' => array(
                'non_existing',
                "Setting 'non_existing' specifies the non-existing file ''."
            ),
            'non-existing file' => array(
                'item_label',
                "Setting 'item_label' specifies the non-existing file '{$this->_fixtureDir}Item Label.dist'."
            )
        );
    }
}
