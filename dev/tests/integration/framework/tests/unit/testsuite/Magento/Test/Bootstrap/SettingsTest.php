<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test class for \Magento\TestFramework\Bootstrap\Settings.
 */
namespace Magento\Test\Bootstrap;

class SettingsTest extends \PHPUnit\Framework\TestCase
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
    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->_fixtureDir = realpath(__DIR__ . '/_files') . '/';
    }

    protected function setUp()
    {
        $this->_object = new \Magento\TestFramework\Bootstrap\Settings(
            $this->_fixtureDir,
            [
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
            ]
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
        new \Magento\TestFramework\Bootstrap\Settings('non_existing_dir', []);
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
        return [
            'string type' => ['item_label', null, 'Item Label'],
            'integer type' => ['number_of_items', null, 42],
            'float type' => ['item_price', null, 12.99],
            'boolean type' => ['is_in_stock', null, true],
            'non-existing' => ['non_existing', null, null],
            'zero string' => ['zero_value', '1', '0'],
            'default value' => ['non_existing', 'default', 'default']
        ];
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
        return [
            'non-enabled string' => ['item_label', false],
            'non-enabled boolean' => ['is_in_stock', false],
            'enabled string' => ['free_shipping', true]
        ];
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
        return [
            'existing file' => ['test_file', '', "{$this->_fixtureDir}metrics.php"],
            'zero value setting' => ['zero_value', 'default_should_be_ignored', "{$this->_fixtureDir}0"],
            'empty default value' => ['non_existing_file', '', ''],
            'zero default value' => ['non_existing_file', '0', "{$this->_fixtureDir}0"],
            'default value' => ['non_existing_file', 'metrics.php', "{$this->_fixtureDir}metrics.php"]
        ];
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
        return [
            'single pattern' => [
                'all_xml_files',
                ["{$this->_fixtureDir}1.xml", "{$this->_fixtureDir}2.xml"],
            ],
            'pattern with braces' => [
                'all_xml_or_one_php_file',
                ["{$this->_fixtureDir}1.xml", "{$this->_fixtureDir}2.xml", "{$this->_fixtureDir}4.php"],
            ],
            'multiple patterns' => [
                'one_xml_or_any_php_file',
                ["{$this->_fixtureDir}1.xml", "{$this->_fixtureDir}4.php"],
            ],
            'non-existing setting' => ['non_existing', []],
            'setting with zero value' => ['zero_value', ["{$this->_fixtureDir}0"]]
        ];
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
        return [
            'config file & dist file' => ['config_file_with_dist', "{$this->_fixtureDir}1.xml"],
            'config file & no dist file' => ['config_file_no_dist', "{$this->_fixtureDir}2.xml"],
            'no config file & dist file' => ['no_config_file_dist', "{$this->_fixtureDir}3.xml.dist"]
        ];
    }

    /**
     * @param string $settingName
     * @param string $expectedExceptionMsg
     * @dataProvider getAsConfigFileExceptionDataProvider
     */
    public function testGetAsConfigFileException($settingName, $expectedExceptionMsg)
    {
        $this->expectException(\Magento\Framework\Exception\LocalizedException::class);
        $this->expectExceptionMessage((string)$expectedExceptionMsg);
        $this->_object->getAsConfigFile($settingName);
    }

    public function getAsConfigFileExceptionDataProvider()
    {
        return [
            'non-existing setting' => [
                'non_existing',
                __("Setting 'non_existing' specifies the non-existing file ''."),
            ],
            'non-existing file' => [
                'item_label',
                __("Setting 'item_label' specifies the non-existing file '%1Item Label.dist'.", $this->_fixtureDir),
            ]
        ];
    }
}
