<?php
/**
 * Scan source code for references to classes and see if they indeed exist
 *
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
 * @subpackage  Integrity
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Integrity_ClassesTest extends PHPUnit_Framework_TestCase
{
    /**
     * List of already found classes to avoid checking them over and over again
     *
     * @var array
     */
    protected static $_existingClasses = array();

    /**
     * @param string $file
     * @dataProvider Util_Files::getPhpFiles
     */
    public function testPhpFile($file)
    {
        self::skipBuggyFile($file);
        $contents = file_get_contents($file);
        $classes = Util_Classes::getAllMatches($contents, '/
            # ::getResourceModel ::getBlockSingleton ::getModel ::getSingleton
            \:\:get(?:ResourceModel | BlockSingleton | Model | Singleton)?\(\s*[\'"]([a-z\d_]+)[\'"]\s*[\),]

            # various methods, first argument
            | \->(?:initReport | addBlock | createBlock | setDataHelperName | getBlockClassName | _?initLayoutMessages
                | setAttributeModel | setBackendModel | setFrontendModel | setSourceModel | setModel
            )\(\s*\'([a-z\d_]+)\'\s*[\),]

            # various methods, second argument
            | \->add(?:ProductConfigurationHelper | OptionsRenderCfg)\(.+?,\s*\'([a-z\d_]+)\'\s*[\),]

            # Mage::helper ->helper
            | (?:Mage\:\:|\->)helper\(\s*\'([a-z\d_]+)\'\s*\)

            # misc
            | function\s_getCollectionClass\(\)\s+{\s+return\s+[\'"]([a-z\d_]+)[\'"]
            | \'resource_model\'\s*=>\s*[\'"]([a-z\d_]+)[\'"]
            | _parentResourceModelName\s*=\s*\'([a-z\d_]+)\'
            /ix'
        );

        // without modifier "i". Starting from capital letter is a significant characteristic of a class name
        Util_Classes::getAllMatches($contents, '/(?:\-> | parent\:\:)(?:_init | setType)\(\s*
                \'([A-Z][a-z\d][A-Za-z\d_]+)\'(?:,\s*\'([A-Z][a-z\d][A-Za-z\d_]+)\')
            \s*\)/x',
            $classes
        );

        $this->_collectResourceHelpersPhp($contents, $classes);

        $this->_assertClassesExist($classes);
    }

    /**
     * Special case: collect resource helper references in PHP-code
     *
     * @param string $contents
     * @param array &$classes
     */
    protected function _collectResourceHelpersPhp($contents, &$classes)
    {
        $matches = Util_Classes::getAllMatches($contents, '/(?:\:\:|\->)getResourceHelper\(\s*\'([a-z\d_]+)\'\s*\)/ix');
        foreach ($matches as $moduleName) {
            $classes[] = "{$moduleName}_Model_Resource_Helper_Mysql4";
        }
    }

    /**
     * @param string $path
     * @dataProvider configFileDataProvider
     */
    public function testConfigFile($path)
    {
        self::skipBuggyFile($path);
        $classes = Util_Classes::collectClassesInConfig(simplexml_load_file($path));
        $this->_assertClassesExist($classes);
    }

    /**
     * @return array
     */
    public function configFileDataProvider()
    {
        return Util_Files::getConfigFiles();
    }

    /**
     * @param string $path
     * @dataProvider Util_Files::getLayoutFiles
     */
    public function testLayoutFile($path)
    {
        self::skipBuggyFile($path);
        $xml = simplexml_load_file($path);

        $classes = Util_Classes::getXmlNodeValues($xml,
            '/layout//*[contains(text(), "_Block_") or contains(text(), "_Model_") or contains(text(), "_Helper_")]'
        );
        foreach (Util_Classes::getXmlAttributeValues($xml, '/layout//@helper', 'helper') as $class) {
            $classes[] = Util_Classes::getCallbackClass($class);
        }
        foreach (Util_Classes::getXmlAttributeValues($xml, '/layout//@module', 'module') as $module) {
            $classes[] = "{$module}_Helper_Data";
        }
        $classes = array_merge($classes, Util_Classes::collectLayoutClasses($xml));

        $this->_assertClassesExist(array_unique($classes));
    }

    /**
     * Determine that some files must be skipped because implementation, broken by some bug
     *
     * @param string $path
     * @return true
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public static function skipBuggyFile($path)
    {
        $path = str_replace(DIRECTORY_SEPARATOR, '/', $path);
        if (strpos($path, 'app/code/core/Mage/XmlConnect/view/frontend/layout.xml')
            || strpos($path, 'app/code/core/Mage/XmlConnect/Block/Checkout/Pbridge/Result.php')
            || strpos($path, 'app/code/core/Mage/XmlConnect/Block/Catalog/Product/Price/Giftcard.php')
            || strpos($path, 'app/code/core/Mage/XmlConnect/Block/Checkout/Payment/Method/List.php')
            || strpos($path, 'app/code/core/Mage/XmlConnect/Block/Catalog/Product/Options/Giftcard.php')
            || strpos($path, 'app/code/core/Mage/XmlConnect/controllers/Paypal/MepController.php')
            || strpos($path, 'app/code/core/Mage/XmlConnect/Block/Catalog/Product/Related.php')
            || strpos($path, 'app/code/core/Mage/XmlConnect/controllers/CartController.php')
            || strpos($path, 'app/code/core/Mage/XmlConnect/Block/Customer/Storecredit.php')
            || strpos($path, 'app/code/core/Mage/XmlConnect/Block/Customer/Storecredit.php')
            || strpos($path, 'app/code/core/Mage/XmlConnect/controllers/PbridgeController.php')
            || strpos($path, 'app/code/core/Mage/XmlConnect/Block/Customer/Address/Form.php')
            || strpos($path, 'app/code/core/Mage/XmlConnect/controllers/CustomerController.php')
        ) {
            self::markTestIncomplete('Bug MMOBAPP-1792');
        }
    }

    /**
     * Check whether specified classes correspond to a file according PSR-0 standard
     *
     * Cyclomatic complexity is because of temporary marking test as incomplete
     * Suppressing "unused variable" because of the "catch" block
     *
     * @param array $classes
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    protected function _assertClassesExist($classes)
    {
        if (!$classes) {
            return;
        }
        $badClasses = array();
        $isBug = false;
        foreach ($classes as $class) {
            try {
                if ('Mage_Catalog_Model_Resource_Convert' == $class) {
                    $isBug = true;
                    continue;
                }
                $path = str_replace('_', DIRECTORY_SEPARATOR, $class) . '.php';
                $this->assertTrue(isset(self::$_existingClasses[$class])
                    || file_exists(PATH_TO_SOURCE_CODE . "/app/code/core/{$path}")
                    || file_exists(PATH_TO_SOURCE_CODE . "/app/code/community/{$path}")
                    || file_exists(PATH_TO_SOURCE_CODE . "/app/code/local/{$path}")
                    || file_exists(PATH_TO_SOURCE_CODE . "/lib/{$path}")
                );
                self::$_existingClasses[$class] = 1;
            } catch (PHPUnit_Framework_AssertionFailedError $e) {
                $badClasses[] = $class;
            }
        }
        if ($badClasses) {
            $this->fail("Missing files with declaration of classes:\n" . implode("\n", $badClasses));
        }
        if ($isBug) {
            $this->markTestIncomplete('Bug MAGE-4763');
        }
    }
}
