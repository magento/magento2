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
 * @package     Mage_Core
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * @group integrity
 */
class Integrity_Modular_LayoutFilesTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider layoutFilesFromModulesDataProvider
     */
    public function testLayoutFilesFromModules($area, $module, $file)
    {
        $this->assertNotEmpty($module);

        $moduleViewDir = Mage::getConfig()->getModuleDir('view', $module);
        $moduleLayoutDir = $moduleViewDir . DIRECTORY_SEPARATOR . $area;

        $this->assertFileExists($moduleViewDir, 'Expected existence of the module view directory.');
        $this->assertFileExists($moduleLayoutDir, 'Expected existence of the module layout directory.');

        $params = array(
            '_area'    => $area,
            '_module'  => $module,
        );
        $layoutFilename = Mage::getDesign()->getFilename($file, $params);

        $this->assertStringStartsWith($moduleLayoutDir, $layoutFilename);
        $this->assertFileExists($layoutFilename, 'Expected existence of the layout file.');
    }

    /**
     * @return array
     */
    public function layoutFilesFromModulesDataProvider()
    {
        $result = array();
        foreach (array('frontend', 'adminhtml') as $area) {
            $updatesRoot = Mage::getConfig()->getNode($area . '/layout/updates');
            foreach ($updatesRoot->children() as $updateNode) {
                $file = (string)$updateNode->file;
                $module = $updateNode->getAttribute('module');
                $result[] = array($area, $module, $file);
            }
        }
        return $result;
    }
}
