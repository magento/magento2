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
 * @category    tests
 * @package     static
 * @subpackage  Legacy
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Test\Integrity;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    protected $_possibleLocales = array('de_DE', 'en_AU', 'en_GB', 'en_US', 'es_ES', 'es_XC', 'fr_FR', 'fr_XC',
        'it_IT', 'ja_JP', 'nl_NL', 'pl_PL', 'zh_CN', 'zh_XC', 'pt_BR');

    public function testExistingFilesDeclared()
    {
        $root = \Magento\TestFramework\Utility\Files::init()->getPathToSource();
        $failures = array();
        foreach (glob("{$root}/app/code/*/*", GLOB_ONLYDIR) as $modulePath) {
            $localeFiles = glob("{$modulePath}/i18n/*.csv");
            foreach ($localeFiles as $file) {
                $file = realpath($file);
                $assertLocale = str_replace('.csv', '', basename($file));
                if (!in_array($assertLocale, $this->_possibleLocales)) {
                    $failures[] = $file;
                }
            }
        }
        $this->assertEmpty($failures,
            'Translation files exist, but not declared in configuration:' . "\n" . var_export($failures, 1));
    }

    public function testPaymentMethods()
    {
        $invoker = new \Magento\TestFramework\Utility\AggregateInvoker($this);
        $invoker(
            /**
             * Verify whether all payment methods are declared in appropriate modules
             */
            function ($configFile, $moduleName) {
                $config = simplexml_load_file($configFile);
                $nodes = $config->xpath('/config/default/payment/*/model') ?: array();
                $formalModuleName = str_replace('_', '\\', $moduleName);
                foreach ($nodes as $node) {
                    $this->assertStringStartsWith(
                        $formalModuleName . '\Model\\',
                        (string)$node,
                        "'$node' payment method is declared in '$configFile' module, "
                            . "but doesn't belong to '$moduleName' module"
                    );
                }
            },
            $this->paymentMethodsDataProvider()
        );
    }

    public function paymentMethodsDataProvider()
    {
        $data = array();
        foreach ($this->_getConfigFilesPerModule() as $configFile => $moduleName) {
            $data[] = array($configFile, $moduleName);
        }
        return $data;
    }

    /**
     * Get list of configuration files associated with modules
     *
     * @return array
     */
    protected function _getConfigFilesPerModule()
    {
        $configFiles = \Magento\TestFramework\Utility\Files::init()->getConfigFiles('config.xml', array(), false);
        $data = array();
        foreach ($configFiles as $configFile) {
            preg_match('#/([^/]+?/[^/]+?)/etc/config\.xml$#', $configFile, $moduleName);
            $moduleName = str_replace('/', '_', $moduleName[1]);
            $data[$configFile] = $moduleName;
        }
        return $data;
    }
}
