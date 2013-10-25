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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
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
        $tmpDir =
            \Magento\TestFramework\Helper\Bootstrap::getInstance()->getAppInstallDir()
                . DIRECTORY_SEPARATOR . 'WizardTest';
        if (is_file($tmpDir)) {
            unlink($tmpDir);
        } elseif (is_dir($tmpDir)) {
            \Magento\Io\File::rmdirRecursive($tmpDir);
        }
        // deliberately create a file instead of directory to emulate broken access to static directory
        touch($tmpDir);
        self::$_tmpDir = $tmpDir;
    }

    public function testPreDispatch()
    {
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->configure(array(
            'preferences' => array(
                'Magento\App\RequestInterface' => 'Magento\TestFramework\Request',
                'Magento\App\Response\Http' => 'Magento\TestFramework\Response'
            )
        ));
        /** @var $appState \Magento\App\State */
        $appState = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\App\State');
        $appState->setInstallDate(false);
        $this->dispatch('install/wizard');
        $this->assertEquals(200, $this->getResponse()->getHttpResponseCode());
        $appState->setInstallDate(date('r', strtotime('now')));
    }

    /**
     * @param string $action
     * @dataProvider actionsDataProvider
     * @expectedException \Magento\BootstrapException
     */
    public function testPreDispatchImpossibleToRenderPage($action)
    {
        $params = self::$_params;
        $params[\Magento\Core\Model\App::PARAM_APP_DIRS][\Magento\App\Dir::STATIC_VIEW] = self::$_tmpDir;
        \Magento\TestFramework\Helper\Bootstrap::getInstance()->reinitialize($params);
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->configure(array(
            'preferences' => array(
                'Magento\App\RequestInterface' => 'Magento\TestFramework\Request',
                'Magento\App\Response\Http' => 'Magento\TestFramework\Response'
            )
        ));
        $this->dispatch("install/wizard/{$action}");
    }

    /**
     * @return array
     */
    public function actionsDataProvider()
    {
        return array(
            array('index'),
            array('begin'),
            array('beginPost'),
            array('locale'),
            array('localeChange'),
            array('localePost'),
            array('download'),
            array('downloadPost'),
            array('downloadAuto'),
            array('install'),
            array('downloadManual'),
            array('config'),
            array('configPost'),
            array('installDb'),
            array('administrator'),
            array('administratorPost'),
            array('end'),
        );
    }
}
