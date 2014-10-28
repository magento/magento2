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
 * Test class for \Magento\TestFramework\Isolation\WorkingDirectory.
 */
namespace Magento\Test\Isolation;

class WorkingDirectoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\TestFramework\Isolation\WorkingDirectory
     */
    protected $_object;

    protected function setUp()
    {
        $this->_object = new \Magento\TestFramework\Isolation\WorkingDirectory();
    }

    protected function tearDown()
    {
        $this->_object = null;
    }

    public function testStartTestEndTest()
    {
        $oldWorkingDir = getcwd();
        $newWorkingDir = __DIR__;
        if ($oldWorkingDir == $newWorkingDir) {
            $this->markTestSkipped("Test requires the current working directory to differ from '{$oldWorkingDir}'.");
        }
        $this->_object->startTest($this);
        chdir($newWorkingDir);
        $this->assertEquals($newWorkingDir, getcwd(), 'Unable to change the current working directory.');
        $this->_object->endTest($this);
        $this->assertEquals($oldWorkingDir, getcwd(), 'Current working directory was not restored.');
    }
}
