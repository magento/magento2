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
namespace Magento\Test\Tools\Layout\Reference;

use Magento\Tools\Layout\Formatter;
use Magento\Tools\Layout\Reference\Processor;

class ProcessorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    protected $_testDir;

    /**
     * @var string
     */
    protected $_varDir;

    /**
     * @var string
     */
    protected $_dictionaryPath;

    /**
     * @var \Magento\Tools\Layout\Reference\Processor
     */
    protected $_processor;

    /**
     * @var \Magento\Tools\Layout\Formatter
     */
    protected $_formatter;

    protected function setUp()
    {
        if (!extension_loaded('xsl')) {
            $this->markTestSkipped('XSL extension needed for XSLT Processor test');
        }
        $this->_testDir = realpath(__DIR__ . '/_files') . '/';

        $filesystem = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->get('Magento\Framework\App\Filesystem');
        $this->_varDir = $filesystem->getPath(\Magento\Framework\App\Filesystem::VAR_DIR) . '/references/';
        mkdir($this->_varDir, 0777, true);

        $this->_formatter = new Formatter();
        $this->_dictionaryPath = $this->_varDir . 'references.xml';

        $this->_processor = new Processor($this->_formatter, $this->_dictionaryPath);
    }

    public function tearDown()
    {
        \Magento\Framework\System\Dirs::rm($this->_varDir);
    }

    public function testGetReferences()
    {
        $this->_processor->getReferences(array($this->_testDir . 'layoutValid.xml'));
        $this->_processor->writeToFile();
        $expected = <<<EOF
<?xml version="1.0"?>
<list>
    <item type="reference" value="block"/>
    <item type="reference" value="container"/>
    <item type="block" value="another.block"/>
    <item type="block" value="block"/>
    <item type="container" value="another.container"/>
    <item type="container" value="container"/>
</list>

EOF;
        $this->assertEquals($expected, file_get_contents($this->_dictionaryPath));
    }

    public function testGetReferencesWithConflictNames()
    {
        $this->_processor->getReferences(array($this->_testDir . 'layoutInvalid.xml'));
        $this->_processor->writeToFile();
        $expected = <<<EOF
<?xml version="1.0"?>
<list>
    <item type="reference" value="block"/>
    <item type="reference" value="broken.reference"/>
    <item type="block" value="another.block"/>
    <item type="block" value="block"/>
    <item type="container" value="block"/>
    <item type="conflictReferences" value="broken.reference"/>
    <item type="conflictNames" value="block"/>
</list>

EOF;
        $this->assertEquals($expected, file_get_contents($this->_dictionaryPath));
    }

    public function testUpdateReferences()
    {
        $testFile = $this->_varDir . 'layoutValid.xml';
        copy($this->_testDir . 'layoutValid.xml', $testFile);

        $layouts = array($testFile);
        $this->_processor->getReferences($layouts);
        $this->_processor->writeToFile();
        $expected = <<<EOF
<?xml version="1.0"?>
<list>
    <item type="reference" value="block"/>
    <item type="reference" value="container"/>
    <item type="block" value="another.block"/>
    <item type="block" value="block"/>
    <item type="container" value="another.container"/>
    <item type="container" value="container"/>
</list>

EOF;
        $this->assertEquals($expected, file_get_contents($this->_dictionaryPath));

        $this->_processor->updateReferences($layouts);
        $this->assertFileEquals($this->_testDir . 'layoutValidExpectUpdated.xml', $testFile);
    }
}
