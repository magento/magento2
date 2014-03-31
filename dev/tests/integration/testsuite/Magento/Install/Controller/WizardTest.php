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
 * @package     Magento_Install
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Install\Controller;

class WizardTest extends \Magento\TestFramework\TestCase\AbstractController
{
    /**
     * @var string
     */
    protected static $_tmpDir;

    /**
     * @var array
     */
    protected static $_params = array();

    public static function setUpBeforeClass()
    {
        /** @var \Magento\App\Filesystem $filesystem */
        $filesystem = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\App\Filesystem');
        $varDirectory = $filesystem->getDirectoryWrite(\Magento\App\Filesystem::VAR_DIR);
        $tmpDir = 'WizardTest';
        $varDirectory->delete($tmpDir);
        // deliberately create a file instead of directory to emulate broken access to static directory
        $varDirectory->touch($tmpDir);

        self::$_tmpDir = $varDirectory->getAbsolutePath($tmpDir);
    }

    public function testPreDispatch()
    {
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->configure(
            array(
                'preferences' => array(
                    'Magento\App\RequestInterface' => 'Magento\TestFramework\Request',
                    'Magento\App\Response\Http' => 'Magento\TestFramework\Response'
                )
            )
        );
        /** @var $appState \Magento\App\State */
        $appState = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\App\State');
        $appState->setInstallDate(false);
        $this->dispatch('install/wizard');
        $this->assertEquals(302, $this->getResponse()->getHttpResponseCode());
        $appState->setInstallDate(date('r', strtotime('now')));
    }
}
