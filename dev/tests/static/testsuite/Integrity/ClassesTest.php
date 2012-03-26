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
     * @dataProvider phpFileDataProvider
     */
    public function testPhpFile($file)
    {
        self::skipBuggyFile($file);
        $contents = file_get_contents($file);
        $classes = Utility_Classes::getAllMatches($contents, '/
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
            | (?:_parentResourceModelName | _checkoutType | _apiType)\s*=\s*\'([a-z\d_]+)\'
            | \'renderer\'\s*=>\s*\'([a-z\d_]+)\'
            /ix'
        );

        // without modifier "i". Starting from capital letter is a significant characteristic of a class name
        Utility_Classes::getAllMatches($contents, '/(?:\-> | parent\:\:)(?:_init | setType)\(\s*
                \'([A-Z][a-z\d][A-Za-z\d_]+)\'(?:,\s*\'([A-Z][a-z\d][A-Za-z\d_]+)\')
            \s*\)/x',
            $classes
        );

        $this->_collectResourceHelpersPhp($contents, $classes);

        $this->_assertClassesExist($classes);
    }

    /**
     * @return array
     */
    public function phpFileDataProvider()
    {
        return Utility_Files::init()->getPhpFiles();
    }

    /**
     * Special case: collect resource helper references in PHP-code
     *
     * @param string $contents
     * @param array &$classes
     */
    protected function _collectResourceHelpersPhp($contents, &$classes)
    {
        $regex = '/(?:\:\:|\->)getResourceHelper\(\s*\'([a-z\d_]+)\'\s*\)/ix';
        $matches = Utility_Classes::getAllMatches($contents, $regex);
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
        $classes = Utility_Classes::collectClassesInConfig(simplexml_load_file($path));
        $this->_assertClassesExist($classes);
    }

    /**
     * @return array
     */
    public function configFileDataProvider()
    {
        return Utility_Files::init()->getConfigFiles();
    }

    /**
     * @param string $path
     * @dataProvider layoutFileDataProvider
     */
    public function testLayoutFile($path)
    {
        self::skipBuggyFile($path);
        $xml = simplexml_load_file($path);

        $classes = Utility_Classes::getXmlNodeValues($xml,
            '/layout//*[contains(text(), "_Block_") or contains(text(), "_Model_") or contains(text(), "_Helper_")]'
        );
        foreach (Utility_Classes::getXmlAttributeValues($xml, '/layout//@helper', 'helper') as $class) {
            $classes[] = Utility_Classes::getCallbackClass($class);
        }
        foreach (Utility_Classes::getXmlAttributeValues($xml, '/layout//@module', 'module') as $module) {
            $classes[] = "{$module}_Helper_Data";
        }
        $classes = array_merge($classes, Utility_Classes::collectLayoutClasses($xml));

        $this->_assertClassesExist(array_unique($classes));
    }

    /**
     * @return array
     */
    public function layoutFileDataProvider()
    {
        return Utility_Files::init()->getLayoutFiles();
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
                $this->assertTrue(isset(self::$_existingClasses[$class])
                    || Utility_Files::init()->codePoolClassFileExists($class)
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
