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
     * @dataProvider areasAndThemesDataProvider
     */
    public function testHandlesHierarchy($area, $package, $theme)
    {
        $xml = $this->_composeXml($area, $package, $theme);

        /**
         * There could be used an xpath "/layouts/*[@type or @owner or @parent]", but it randomly produced bugs, by
         * selecting all nodes in depth. Thus it was refactored into manual nodes extraction.
         */
        $handles = array();
        foreach ($xml->children() as $handleNode) {
            if ($handleNode->getAttribute('type')
                || $handleNode->getAttribute('owner')
                || $handleNode->getAttribute('parent')
            ) {
                $handles[] = $handleNode;
            }
        }

        /** @var Mage_Core_Model_Layout_Element $node */
        $errors = array();
        foreach ($handles as $node) {
            $this->_collectHierarchyErrors($node, $xml, $errors);
        }

        if ($errors) {
            $this->fail("There are errors while checking the page type and fragment types hierarchy at:\n"
                . var_export($errors, 1)
            );
        }
    }

    /**
     * Composes full layout xml for designated parameters
     *
     * @param string $area
     * @param string $package
     * @param string $theme
     * @return Mage_Core_Model_Layout_Element
     */
    protected function _composeXml($area, $package, $theme)
    {
        $layoutUpdate = new Mage_Core_Model_Layout_Update(array(
            'area' => $area, 'package' => $package, 'theme' => $theme
        ));
        return $layoutUpdate->getFileLayoutUpdatesXml();
    }

    /**
     * Validate node's declared position in hierarchy and add errors to the specified array if found
     *
     * @param SimpleXMLElement $node
     * @param Mage_Core_Model_Layout_Element $xml
     * @param array &$errors
     */
    protected function _collectHierarchyErrors($node, $xml, &$errors)
    {
        $name = $node->getName();
        $refName = $node->getAttribute('type') == Mage_Core_Model_Layout_Update::TYPE_FRAGMENT
            ? $node->getAttribute('owner') : $node->getAttribute('parent');
        if ($refName) {
            $refNode = $xml->xpath("/layouts/{$refName}");
            if (!$refNode) {
                $errors[$name][] = "Node '{$refName}', referenced in hierarchy, does not exist";
            } elseif ($refNode[0]->getAttribute('type') == Mage_Core_Model_Layout_Update::TYPE_FRAGMENT) {
                $errors[$name][] = "Page fragment type '{$refName}', cannot be an ancestor in a hierarchy";
            }
        }
    }

    /**
     * List all themes available in the system
     *
     * The "no theme" (false) is prepended to the result -- it means layout updates must be loaded from modules
     *
     * A test that uses such data provider is supposed to gather view resources in provided scope
     * and analyze their integrity. For example, merge and verify all layouts in this scope.
     *
     * Such tests allow to uncover complicated code integrity issues, that may emerge due to view fallback mechanism.
     * For example, a module layout file is overlapped by theme layout, which has mistakes.
     * Such mistakes can be uncovered only when to emulate this particular theme.
     * Also emulating "no theme" mode allows to detect inversed errors: when there is a view file with mistake
     * in a module, but it is overlapped by every single theme by files without mistake. Putting question of code
     * duplication aside, it is even more important to detect such errors, than an error in a single theme.
     *
     * @return array
     */
    public function areasAndThemesDataProvider()
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

    /**
     * @param string $area
     * @param string $package
     * @param string $theme
     * @dataProvider areasAndThemesDataProvider
     */
    public function testHandleLabels($area, $package, $theme)
    {
        $xml = $this->_composeXml($area, $package, $theme);

        $xpath = '/layouts/*['
            . '@type="' . Mage_Core_Model_Layout_Update::TYPE_PAGE . '"'
            . ' or @type="' . Mage_Core_Model_Layout_Update::TYPE_FRAGMENT . '"'
            . ' or @translate="label"]';
        $handles = $xml->xpath($xpath) ?: array();

        /** @var Mage_Core_Model_Layout_Element $node */
        $errors = array();
        foreach ($handles as $node) {
            if (!$node->xpath('label')) {
                $errors[] = $node->getName();
            }
        }
        if ($errors) {
            $this->fail("The following handles must have label, but they don't have it:\n" . var_export($errors, 1)
            );
        }
    }
}
