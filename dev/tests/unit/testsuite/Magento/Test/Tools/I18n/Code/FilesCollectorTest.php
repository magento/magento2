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
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Test\Tools\I18n\Code;

class FilesCollectorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    protected $_testDir;

    /**
     * @var \Magento\Tools\I18n\Code\FilesCollector
     */
    protected $_filesCollector;

    protected function setUp()
    {
        // dev/tests/unit/testsuite/tools/I18n/Code/_files/files_collector
        $this->_testDir = str_replace('\\', '/', realpath(dirname(__FILE__))) . '/_files/files_collector/';

        $objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->_filesCollector = $objectManagerHelper->getObject('Magento\Tools\I18n\Code\FilesCollector');
    }

    public function testGetFilesWithoutMask()
    {
        $expectedResult = array($this->_testDir . 'default.xml', $this->_testDir . 'file.js');
        $files = $this->_filesCollector->getFiles(array($this->_testDir));
        $this->assertEquals($expectedResult, $files);
    }

    public function testGetFilesWithMask()
    {
        $expectedResult = array($this->_testDir . 'file.js');
        $this->assertEquals($expectedResult, $this->_filesCollector->getFiles(array($this->_testDir), '/\.js$/'));
    }
}
