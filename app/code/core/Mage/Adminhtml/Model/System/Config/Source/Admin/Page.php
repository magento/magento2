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
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Admin system config sturtup page
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Model_System_Config_Source_Admin_Page
{
    protected $_url;

    public function toOptionArray()
    {
        $options = array();
        $menu    = $this->_buildMenuArray();

        $this->_createOptions($options, $menu);

        return $options;
    }

    protected function _createOptions(&$optionArray, $menuNode)
    {
        $nonEscapableNbspChar = html_entity_decode('&#160;', ENT_NOQUOTES, 'UTF-8');

        foreach ($menuNode as $menu) {

            if (!empty($menu['url'])) {
                $optionArray[] = array(
                    'label' => str_repeat($nonEscapableNbspChar, ($menu['level'] * 4)) . $menu['label'],
                    'value' => $menu['path'],
                );

                if (isset($menu['children'])) {
                    $this->_createOptions($optionArray, $menu['children']);
                }
            }
            else {
                $children = array();

                if(isset($menu['children'])) {
                    $this->_createOptions($children, $menu['children']);
                }

                $optionArray[] = array(
                    'label' => str_repeat($nonEscapableNbspChar, ($menu['level'] * 4)) . $menu['label'],
                    'value' => $children,
                );
            }
        }
    }

    protected function _getUrlModel()
    {
        if (is_null($this->_url)) {
            $this->_url = Mage::getModel('Mage_Adminhtml_Model_Url');
        }
        return $this->_url;
    }

    protected function _buildMenuArray(Varien_Simplexml_Element $parent=null, $path='', $level=0)
    {
        if (is_null($parent)) {
            $parent = Mage::getSingleton('Mage_Admin_Model_Config')->getAdminhtmlConfig()->getNode('menu');
        }

        $parentArr = array();
        $sortOrder = 0;
        foreach ($parent->children() as $childName=>$child) {

            if ($child->depends && !$this->_checkDepends($child->depends)) {
                continue;
            }

            $menuArr = array();
            $menuArr['label'] = $this->_getHelperValue($child);

            $menuArr['sort_order'] = $child->sort_order ? (int)$child->sort_order : $sortOrder;

            if ($child->action) {
                $menuArr['url'] = (string)$child->action;
            } else {
                $menuArr['url'] = '';
            }

            $menuArr['level'] = $level;
            $menuArr['path'] = $path . $childName;

            if ($child->children) {
                $menuArr['children'] = $this->_buildMenuArray($child->children, $path.$childName.'/', $level+1);
            }
            $parentArr[$childName] = $menuArr;

            $sortOrder++;
        }

        uasort($parentArr, array($this, '_sortMenu'));

        while (list($key, $value) = each($parentArr)) {
            $last = $key;
        }
        if (isset($last)) {
            $parentArr[$last]['last'] = true;
        }

        return $parentArr;
    }

    protected function _sortMenu($a, $b)
    {
        return $a['sort_order']<$b['sort_order'] ? -1 : ($a['sort_order']>$b['sort_order'] ? 1 : 0);
    }

    protected function _checkDepends(Varien_Simplexml_Element $depends)
    {
        if ($depends->module) {
            $modulesConfig = Mage::getConfig()->getNode('modules');
            foreach ($depends->module as $module) {
                if (!$modulesConfig->$module || !$modulesConfig->$module->is('active')) {
                    return false;
                }
            }
        }

        return true;
    }

    protected function _getHelperValue(Varien_Simplexml_Element $child)
    {
        $helperName         = 'Mage_Adminhtml_Helper_Data';
        $titleNodeName      = 'title';
        $childAttributes    = $child->attributes();
        if (isset($childAttributes['module'])) {
            $helperName     = (string)$childAttributes['module'];
        }

        $titleNodeName = 'title';

        return Mage::helper($helperName)->__((string)$child->$titleNodeName);
    }
}
