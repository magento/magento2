<?php
/**
 * Layout nodes integrity tests
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
 * @package     integration
 * @subpackage  integrity
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Integrity_LayoutTest extends PHPUnit_Framework_TestCase
{
    /**
     * @param string $area
     * @param string $package
     * @param string $theme
     * @dataProvider handleHierarchyDataProvider
     */
    public function testHandleHierarchy($area, $package, $theme)
    {
        $layoutUpdate = new Mage_Core_Model_Layout_Update(array(
            'area' => $area, 'package' => $package, 'theme' => $theme
        ));
        $xml = $layoutUpdate->getFileLayoutUpdatesXml();
        $handles = $xml->xpath('/layouts/*[@parent]') ?: array();
        /** @var Mage_Core_Model_Layout_Element $node */
        $errors = array();
        foreach ($handles as $node) {
            $parent = $node->getAttribute('parent');
            if (!$xml->xpath("/layouts/{$parent}")) {
                $errors[$node->getName()] = $parent;
            }
        }
        if ($errors) {
            $this->fail("Reference(s) to non-existing parent handle found at:\n" . var_export($errors, 1));
        }
    }

    /**
     * @return array
     */
    public function handleHierarchyDataProvider()
    {
        $result = array();
        foreach (array('adminhtml', 'frontend', 'install') as $area) {
            $result[] = array($area, false, false);
            foreach (Mage::getDesign()->getDesignEntitiesStructure($area, false) as $package => $themes) {
                foreach (array_keys($themes) as $theme) {
                    $result[] = array($area, $package, $theme);
                }
            }
        }
        return $result;
    }
}
