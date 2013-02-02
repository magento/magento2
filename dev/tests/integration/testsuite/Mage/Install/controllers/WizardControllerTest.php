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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Install_WizardControllerTest extends Magento_Test_TestCase_ControllerAbstract
{
    /**
     * @var string
     */
    protected static $_mediaDir;

    /**
     * @var string
     */
    protected static $_themeDir;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        self::$_mediaDir = Mage::getBaseDir(Mage_Core_Model_Dir::MEDIA);
        self::$_themeDir = self::$_mediaDir . DIRECTORY_SEPARATOR . 'theme';
    }

    public function setUp()
    {
        parent::setUp();
        // emulate non-installed application
        $this->_runOptions[Mage_Core_Model_Config::INIT_OPTION_EXTRA_DATA]
            = sprintf(Mage_Core_Model_Config::CONFIG_TEMPLATE_INSTALL_DATE, 'invalid');
    }

    public function tearDown()
    {
        if (is_dir(self::$_mediaDir)) {
            chmod(self::$_mediaDir, 0777);
        }
        if (is_dir(self::$_themeDir)) {
            chmod(self::$_themeDir, 0777);
        }
        parent::tearDown();
    }

    public function testPreDispatch()
    {
        $this->dispatch('install/wizard');
        $this->assertEquals(200, $this->getResponse()->getHttpResponseCode());
    }

    public function testPreDispatchNonWritableMedia()
    {
        $this->_testInstallProhibitedWhenNonWritable(self::$_mediaDir);
    }

    public function testPreDispatchNonWritableTheme()
    {
        $this->_testInstallProhibitedWhenNonWritable(self::$_themeDir);
    }

    /**
     * Tests that when $nonWritableDir folder is read-only, the installation controller prohibits continuing
     * installation and points to fix issue with theme directory.
     *
     * @param string $nonWritableDir
     */
    protected function _testInstallProhibitedWhenNonWritable($nonWritableDir)
    {
        if (file_exists($nonWritableDir) && !is_dir($nonWritableDir)) {
            $this->markTestSkipped("Incorrect file structure. $nonWritableDir should be a directory");
        }

        if (is_dir($nonWritableDir)) {
            chmod($nonWritableDir, 0444);
        } else {
            mkdir($nonWritableDir, 0444);
        }

        if (is_writable($nonWritableDir)) {
            $this->markTestSkipped("Current OS doesn't support setting write-access for folders via mode flags");
        }

        $this->dispatch('install/wizard');

        $this->assertEquals(503, $this->getResponse()->getHttpResponseCode());
        $this->assertContains(self::$_themeDir, $this->getResponse()->getBody());
    }
}
