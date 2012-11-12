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
 * @package     Mage_XmlConnect
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Application configuration renderer
 *
 * @category    Mage
 * @package     Mage_XmlConnect
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_XmlConnect_Block_Configuration extends Mage_Core_Block_Template
{
    /**
     * Current application model
     *
     * @var Mage_XmlConnect_Model_Application
     */
    protected $_app;

    /**
     * Init current application
     *
     * @return Mage_XmlConnect_Block_Configuration
     */
    protected function _beforeToHtml()
    {
        $app = Mage::helper('Mage_XmlConnect_Helper_Data')->getApplication();
        if ($app) {
            $this->_app = $app;
        } else {
            $this->_app = Mage::getModel('Mage_XmlConnect_Model_Application');
            $this->_app->loadDefaultConfiguration();
        }
        return $this;
    }

    /**
     * Recursively build XML configuration tree
     *
     * @param Mage_XmlConnect_Model_Simplexml_Element $section
     * @param array $subtree
     * @return Mage_XmlConnect_Model_Simplexml_Element
     */
    protected function _buildRecursive($section, $subtree)
    {
        Mage::helper('Mage_XmlConnect_Helper_Data')->getDeviceHelper()->checkRequiredConfigFields($subtree);

        foreach ($subtree as $key => $value) {
            if (is_array($value)) {
                if ($key == 'fonts') {
                    $subsection = $section->addChild('fonts');
                    foreach ($value as $label=>$v) {
                        if (empty($v['name']) || empty($v['size']) || empty($v['color'])) {
                            continue;
                        }
                        $font = $subsection->addChild('font');
                        $font->addAttribute('label', $label);
                        $font->addAttribute('name', $v['name']);
                        $font->addAttribute('size', $v['size']);
                        $font->addAttribute('color', $v['color']);
                    }
                } elseif ($key == 'pages') {
                    $subsection = $section->addChild('content');
                    foreach ($value as $page) {
                        $this->_buildRecursive($subsection->addChild('page'), $page);
                    }
                } else {
                    $subsection = $section->addChild($key);
                    $this->_buildRecursive($subsection, $value);
                }
            } elseif ($value instanceof Mage_XmlConnect_Model_Tabs) {
                foreach ($value->getRenderTabs() as $tab) {
                    $subsection = $section->addChild('tab');
                    $this->_buildRecursive($subsection, $tab);
                }
            } else {
                $value = (string)$value;
                if ($value != '') {
                    $section->addChild($key, Mage::helper('Mage_Core_Helper_Data')->escapeHtml($value));
                }
            }
        }
    }

    /**
     * Render block
     *
     * @return string
     */
    protected function _toHtml()
    {
        $xml = Mage::getModel('Mage_XmlConnect_Model_Simplexml_Element',
            array('data' => '<configuration></configuration>'));
        $this->_buildRecursive($xml,
            Mage::helper('Mage_XmlConnect_Helper_Data')->excludeXmlConfigKeys($this->_app->getRenderConf())
        );
        return $xml->asNiceXml();
    }
}
