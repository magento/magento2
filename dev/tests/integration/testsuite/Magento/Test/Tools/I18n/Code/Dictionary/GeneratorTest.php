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
namespace Magento\Test\Tools\I18n\Code\Dictionary;

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
    protected $_source;

    /**
     * @var string
     */
    protected $_outputFileName;

    /**
     * @var \Magento\Tools\I18n\Code\Dictionary\Generator
     */
    protected $_generator;

    /**
     * @var array
     */
    protected $_filesOptions;

    protected function setUp()
    {
        $this->_testDir = realpath(__DIR__ . '/_files');
        $this->_expectedDir = $this->_testDir . '/expected';
        $this->_source = $this->_testDir . '/source';
        $this->_filesOptions = array(
            array(
                'type' => 'php',
                'paths' => array($this->_source . '/app/code/', $this->_source . '/app/design/'),
                'fileMask' => '/\.(php|phtml)$/'
            ),
            array(
                'type' => 'js',
                'paths' => array(
                    $this->_source . '/app/code/',
                    $this->_source . '/app/design/',
                    $this->_source . '/pub/lib/mage/',
                    $this->_source . '/pub/lib/varien/'
                ),
                'fileMask' => '/\.(js|phtml)$/'
            ),
            array(
                'type' => 'xml',
                'paths' => array($this->_source . '/app/code/', $this->_source . '/app/design/'),
                'fileMask' => '/\.xml$/'
            )
        );
        $this->_outputFileName = $this->_testDir . '/translate.csv';

        $this->_generator = ServiceLocator::getDictionaryGenerator();
    }

    protected function tearDown()
    {
        if (file_exists($this->_outputFileName)) {
            unlink($this->_outputFileName);
        }
    }

    public function testGenerationWithoutContext()
    {
        $this->_generator->generate($this->_filesOptions, $this->_outputFileName);

        $this->assertFileEquals($this->_expectedDir . '/without_context.csv', $this->_outputFileName);
    }

    public function testGenerationWithContext()
    {
        $this->_generator->generate($this->_filesOptions, $this->_outputFileName, true);

        $this->assertFileEquals($this->_expectedDir . '/with_context.csv', $this->_outputFileName);
    }
}
