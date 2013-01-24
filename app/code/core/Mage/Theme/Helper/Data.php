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
 * @package     Mage_Theme
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Theme data helper
 */
class Mage_Theme_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Get CSS files of a given theme
     *
     * Returned array has a structure
     * array(
     *   'Mage_Catalog::widgets.css' => 'http://mage2.com/pub/media/theme/frontend/_theme15/en_US/Mage_Cms/widgets.css'
     * )
     *
     * @param Mage_Core_Model_Theme $theme
     * @return array
     */
    public function getCssFiles($theme)
    {
        $arguments = array(
            'area'  => $theme->getArea(),
            'theme' => $theme->getId()
        );
        /** @var $layoutMerge Mage_Core_Model_Layout_Merge */
        $layoutMerge = Mage::getModel('Mage_Core_Model_Layout_Merge', array('arguments' => $arguments));
        $layoutElement = $layoutMerge->getFileLayoutUpdatesXml();
        
        $xpathRefs = '//reference[@name="head"]/action[@method="addCss" or @method="addCssIe"]/*[1]';
        $xpathBlocks = '//block[@type="Mage_Page_Block_Html_Head"]/action[@method="addCss" or @method="addCssIe"]/*[1]';
        $files = array_merge(
            $layoutElement->xpath($xpathRefs),
            $layoutElement->xpath($xpathBlocks)
        );

        $design = Mage::getDesign();
        $params = array(
            'area'       => $theme->getArea(),
            'themeModel' => $theme,
            'skipProxy'  => true
        );
        $urls = array();
        foreach ($files as $file) {
            $urls[(string)$file] = $design->getViewFile($file, $params);
        }

        return $urls;
    }
}
