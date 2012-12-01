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
 * @package     Mage_Core
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Core data helper
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Core_Helper_Translate extends Mage_Core_Helper_Abstract
{
    /**
     * Save translation data to database for specific area
     *
     * @param array  $translate
     * @param string $area
     * @param string $returnType
     * @return string
     */
    public function apply($translate, $area, $returnType = 'json')
    {
        try {
            if ($area) {
                Mage::getDesign()->setArea($area);
            }
            Mage::getModel('Mage_Core_Model_Translate_Inline')->processAjaxPost($translate);
            return $returnType == 'json' ? "{success:true}" : true;
        } catch (Exception $e) {
            return $returnType == 'json' ? "{error:true,message:'" . $e->getMessage() . "'}" : false;
        }
    }

    /**
     * Compose locale inheritance hierarchy based on given config
     *
     * @param array $localeConfig assoc array where key is a code of locale and value is a code of its parent locale
     * @return array
     */
    public function composeLocaleHierarchy(array $localeConfig)
    {
        $localeHierarchy = array();
        foreach ($localeConfig as $locale => $localeParent) {
            $localeParents = array($localeParent);
            while (isset($localeConfig[$localeParent]) && !in_array($localeConfig[$localeParent], $localeParents)
                && $locale != $localeConfig[$localeParent]
            ) {
                // inheritance chain starts with the deepest parent
                array_unshift($localeParents, $localeConfig[$localeParent]);
                $localeParent = $localeConfig[$localeParent];
            }
            // store hierarchy for current locale
            $localeHierarchy[$locale] = $localeParents;
        }
        return $localeHierarchy;
    }
}
