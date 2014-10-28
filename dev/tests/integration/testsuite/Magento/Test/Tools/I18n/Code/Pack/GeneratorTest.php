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
namespace Magento\Test\Tools\I18n\Code\Pack;

use Magento\Tools\I18n\Code\ServiceLocator;

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
     * @var \Magento\Tools\I18n\Code\Pack\Generator
     */
    protected $_generator;

    protected function setUp()
    {
        $this->_testDir = realpath(__DIR__ . '/_files');
        $this->_expectedDir = $this->_testDir . '/expected';
        $this->_dictionaryPath = $this->_testDir . '/source.csv';
        $this->_packPath = $this->_testDir . '/pack';
        $this->_locale = 'de_DE';
        $this->_expectedFiles = array(
            "/app/code/Magento/FirstModule/i18n/{$this->_locale}.csv",
            "/app/code/Magento/SecondModule/i18n/{$this->_locale}.csv",
            "/app/design/adminhtml/default/i18n/{$this->_locale}.csv",
            "/lib/web/i18n/{$this->_locale}.csv",
        );

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
