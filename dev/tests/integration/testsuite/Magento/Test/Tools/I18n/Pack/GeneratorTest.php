<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Test\Tools\I18n\Pack;

use Magento\Tools\I18n\ServiceLocator;

class GeneratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    protected $_testDir;

    /**
     * @var string
     */
    protected $_expectedDir;

    /**
     * @var string
     */
    protected $_dictionaryPath;

    /**
     * @var string
     */
    protected $_packPath;

    /**
     * @var string
     */
    protected $_locale;

    /**
     * @var array
     */
    protected $_expectedFiles;

    /**
     * @var \Magento\Tools\I18n\Pack\Generator
     */
    protected $_generator;

    protected function setUp()
    {
        $this->_testDir = realpath(__DIR__ . '/_files');
        $this->_expectedDir = $this->_testDir . '/expected';
        $this->_dictionaryPath = $this->_testDir . '/source.csv';
        $this->_packPath = $this->_testDir . '/pack';
        $this->_locale = 'de_DE';
        $this->_expectedFiles = [
            "/app/code/Magento/FirstModule/i18n/{$this->_locale}.csv",
            "/app/code/Magento/SecondModule/i18n/{$this->_locale}.csv",
            "/app/design/adminhtml/default/i18n/{$this->_locale}.csv",
            "/lib/web/i18n/{$this->_locale}.csv",
        ];

        $this->_generator = ServiceLocator::getPackGenerator();

        \Magento\Framework\System\Dirs::rm($this->_packPath);
    }

    protected function tearDown()
    {
        \Magento\Framework\System\Dirs::rm($this->_packPath);
    }

    public function testGeneration()
    {
        $this->assertFileNotExists($this->_packPath);

        $this->_generator->generate($this->_dictionaryPath, $this->_packPath, $this->_locale);

        foreach ($this->_expectedFiles as $file) {
            $this->assertFileEquals($this->_expectedDir . $file, $this->_packPath . $file);
        }
    }
}
