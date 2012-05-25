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

        $xpath = '/layouts/*[(@type) or (@parent) or (@owner)]'; // Bugs with xpath, added brackets to overcome
        $handles = $xml->xpath($xpath) ?: array();

        /** @var Mage_Core_Model_Layout_Element $node */
        $errors = array();
        foreach ($handles as $node) {
            $error = $this->_validatePageNodeInHierarchy($node, $xml);
            if ($error) {
                $index = $node->getName();
                if (!isset($errors[$index])) {
                    $errors[$index] = array();
                }
                $errors[$index][] = $error;
            }
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
     * Validates node's declared position in hierarchy. Returns error description, if something is wrong.
     *
     * @param SimpleXMLElement $node
     * @param Mage_Core_Model_Layout_Element $xml
     * @return string|false
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function _validatePageNodeInHierarchy($node, $xml)
    {
        $type = $node->getAttribute('type');
        $parent = $node->getAttribute('parent');
        $owner = $node->getAttribute('owner');

        switch ($type) {
            case Mage_Core_Model_Layout_Update::TYPE_PAGE:
                if ($owner) {
                    return 'Attribute "owner" is not appropriate for page types';
                }
                $refName = $parent;
                break;
            case Mage_Core_Model_Layout_Update::TYPE_FRAGMENT:
                if ($parent) {
                    return 'Attribute "parent" is not appropriate for page fragment types';
                }
                if (!$owner) {
                    return 'No owner specified for page fragment type handle';
                }
                $refName = $owner;
                break;
            default:
                return "Unknown handle type: {$type}";
        }

        if ($refName) {
            $refNode = $xml->xpath("/layouts/{$refName}");
            if (!$refNode || !count($refNode)) {
                return "Node '{$refName}', referenced in hierarchy, does not exist";
            }
            if ($refNode[0]->getAttribute('type') == Mage_Core_Model_Layout_Update::TYPE_FRAGMENT) {
                return "Page fragment type '{$refName}', cannot be an ancestor in a hierarchy";
            }
        }
        return false;
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
