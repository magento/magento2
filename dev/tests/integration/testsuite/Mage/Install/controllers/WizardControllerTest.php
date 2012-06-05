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
 * @package     Mage_Install
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Install_WizardControllerTest extends Magento_Test_TestCase_ControllerAbstract
{
    /**
     * @var string
     */
    protected static $_tmpMediaDir;

    /**
     * @var string
     */
    protected static $_tmpSkinDir;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        self::$_tmpMediaDir = realpath(Magento_Test_Bootstrap::getInstance()->getTmpDir())
            . DIRECTORY_SEPARATOR . 'media';
        self::$_tmpSkinDir = self::$_tmpMediaDir . DIRECTORY_SEPARATOR . 'skin';
    }

    public function setUp()
    {
        parent::setUp();
        $this->_runOptions['is_installed'] = false;
    }

    public function tearDown()
    {
        Varien_Io_File::rmdirRecursive(self::$_tmpMediaDir);
        parent::tearDown();
    }

    public function testPreDispatch()
    {
        $this->dispatch('install/index');
        $this->assertEquals(200, $this->getResponse()->getHttpResponseCode());
    }

    public function testPreDispatchNonWritableMedia()
    {
        mkdir(self::$_tmpMediaDir, 0444);
        $this->_runOptions['media_dir'] = self::$_tmpMediaDir;
        $this->_runOptions['skin_dir'] = self::$_tmpSkinDir;

        $this->_testInstallProhibitedWhenNonWritable(self::$_tmpMediaDir);
    }

    public function testPreDispatchNonWritableSkin()
    {
        mkdir(self::$_tmpMediaDir, 0777);
        $this->_runOptions['media_dir'] = self::$_tmpMediaDir;
        $this->_runOptions['skin_dir'] = self::$_tmpSkinDir;

        mkdir(self::$_tmpSkinDir, 0444);
        $this->_testInstallProhibitedWhenNonWritable(self::$_tmpSkinDir);
    }

    /**
     * Tests that when $nonWritableDir folder is read-only, the installation controller prohibits continuing
     * installation and points to fix issue with skin directory.
     *
     * @param string $nonWritableDir
     */
    protected function _testInstallProhibitedWhenNonWritable($nonWritableDir)
    {
        if (is_writable($nonWritableDir)) {
            $this->markTestSkipped("Current OS doesn't support setting write-access for folders via mode flags");
        }

        $this->dispatch('install/index');

        $this->assertEquals(503, $this->getResponse()->getHttpResponseCode());
        $this->assertContains(self::$_tmpSkinDir, $this->getResponse()->getBody());
    }
}
